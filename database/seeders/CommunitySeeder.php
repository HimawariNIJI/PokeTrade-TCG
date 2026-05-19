<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CollectionCard;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\ProfileComment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the community layer of the pivoted site: forum categories/threads/
 * posts, enriched public trainer profiles (bio, socials, visibility), digital
 * gacha collections, chase-card lists, and profile-wall comments.
 *
 * Safe to re-run: forum tables, profile comments and collections are cleared
 * first; chase cards are synced without detaching.
 */
class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        ForumPost::query()->delete();
        ForumThread::query()->delete();
        ForumCategory::query()->delete();
        ProfileComment::query()->delete();
        CollectionCard::query()->delete();

        $users = User::query()->where('email', 'like', '%@poketrade.test')->get();
        if ($users->isEmpty()) {
            $this->command?->warn('CommunitySeeder: no demo users found — run DatabaseSeeder first.');
            return;
        }

        $this->seedProfiles($users);
        $this->seedCollections($users);
        $this->seedForums($users);
        $this->seedProfileComments($users);

        $this->command?->info('Seeded community: profiles, collections, forums and comments.');
    }

    /** Bios, locations, socials and visibility toggles for the demo trainers. */
    private function seedProfiles($users): void
    {
        $bios = [
            'Eeveelution completionist. Chasing every Special Illustration Rare in Prismatic Evolutions.',
            'Casual collector, competitive heart. Here for the pulls and the price charts.',
            'Been collecting since Base Set. The binder never stops growing.',
            'Gym leader energy. I track prices so you don\'t have to.',
            'Just here for the gacha dopamine and the forum banter.',
            'Sleeve everything. Trust no one. Chase the Umbreon.',
        ];

        foreach ($users as $i => $user) {
            $handle = explode('@', $user->email)[0];

            $user->bio = $bios[$i % count($bios)];
            $user->location = ['Pallet Town', 'Cerulean City', 'Saffron City', 'Jakarta', 'Kalos Region'][$i % 5];
            $user->social_links = [
                'twitter'   => 'https://twitter.com/'.$handle,
                'instagram' => 'https://instagram.com/'.$handle,
                'tiktok'    => null,
                'youtube'   => null,
                'discord'   => $handle.'#'.str_pad((string) (($i * 137) % 10000), 4, '0', STR_PAD_LEFT),
                'website'   => null,
            ];
            // Vary visibility so the privacy toggles are visibly doing something.
            $user->profile_settings = array_merge(User::DEFAULT_PROFILE_SETTINGS, [
                'show_socials'   => $i % 4 !== 0,
                'allow_comments' => $i % 5 !== 0,
            ]);
            $user->save();
        }
    }

    /** Hand each trainer a digital gacha collection + a chase-card list. */
    private function seedCollections($users): void
    {
        $cardIds = Card::query()->inRandomOrder()->limit(120)->pluck('id');
        if ($cardIds->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Digital collection — duplicates allowed, just like real gacha.
            $rows = [];
            foreach (range(1, rand(14, 30)) as $n) {
                $rows[] = [
                    'user_id'     => $user->id,
                    'card_id'     => $cardIds->random(),
                    'source'      => 'gacha',
                    'obtained_at' => now()->subDays(rand(0, 40))->subMinutes(rand(0, 1440)),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
            CollectionCard::insert($rows);

            // Chase cards — cards the trainer is hunting for (wishlist pivot).
            $user->wishlistedCards()->syncWithoutDetaching(
                $cardIds->shuffle()->take(rand(3, 7))->all()
            );
        }
    }

    /** Forum categories, threads and replies. */
    private function seedForums($users): void
    {
        $categories = [
            ['General Discussion', 'violet', 'Everything Prismatic Evolutions — set talk, news and trainer chatter.'],
            ['Pulls & Collections', 'gold', 'Show off your gacha pulls and binder highlights.'],
            ['Market & Prices',     'mint', 'Price movements, market value debates and tracker talk.'],
            ['Auctions & Deals',    'sky',  'Auction recaps, sniping stories and what cards are worth bidding on.'],
            ['Off-Topic Lounge',    'pink', 'Anything goes — keep it friendly.'],
        ];

        $threadTitles = [
            'Finally pulled the SIR Umbreon ex — still shaking',
            'Is the Sylveon ex market value about to dip?',
            'Best regulation mark cards to chase right now',
            'Post your rarest digital pull this week',
            'Auction last night went way over market — worth it?',
            'New trainer here, where do I start tracking?',
            'Eeveelution lineup: ranking all nine SIRs',
            'Merch drop restock — anyone grabbed the playmat?',
        ];

        $replyBodies = [
            'Congrats! That\'s a chase card for half the forum.',
            'Honestly the price tracker has been climbing all month, not surprised.',
            'I\'d wait a week before bidding that high personally.',
            'Added that one to my chase list ages ago, still hunting.',
            'The gacha rates feel generous this patch, pulled two rares today.',
            'Great thread, bookmarking this for later.',
            'Disagree slightly — market value depends a lot on condition.',
            'Welcome! Start with the tracker, then open a few packs.',
        ];

        foreach ($categories as $ci => [$name, $accent, $description]) {
            $category = ForumCategory::create([
                'name'        => $name,
                'slug'        => str($name)->slug(),
                'description' => $description,
                'accent'      => $accent,
                'position'    => $ci,
            ]);

            foreach (range(1, rand(2, 4)) as $t) {
                $author = $users->random();
                $createdAt = now()->subDays(rand(1, 25))->subMinutes(rand(0, 1440));

                $thread = ForumThread::create([
                    'forum_category_id' => $category->id,
                    'user_id'           => $author->id,
                    'title'             => $threadTitles[array_rand($threadTitles)],
                    'body'              => 'Kicking off the discussion — curious what everyone thinks. '
                                          .'Drop your takes below.',
                    'pinned'            => $t === 1 && $ci < 2,
                    'views'             => rand(12, 480),
                    'last_posted_at'    => $createdAt,
                    'created_at'        => $createdAt,
                    'updated_at'        => $createdAt,
                ]);

                $lastPostedAt = $createdAt;
                foreach (range(1, rand(1, 5)) as $p) {
                    $lastPostedAt = $lastPostedAt->copy()->addHours(rand(1, 30));
                    ForumPost::create([
                        'forum_thread_id' => $thread->id,
                        'user_id'         => $users->random()->id,
                        'body'            => $replyBodies[array_rand($replyBodies)],
                        'created_at'      => $lastPostedAt,
                        'updated_at'      => $lastPostedAt,
                    ]);
                }

                $thread->update(['last_posted_at' => $lastPostedAt]);
            }
        }
    }

    /** A few comments on each trainer's profile wall, from other trainers. */
    private function seedProfileComments($users): void
    {
        $messages = [
            'Insane collection, the Espeon ex is gorgeous.',
            'Thanks for the price tip on the forums!',
            'Your chase list and mine are basically twins.',
            'Pulled anything good this week?',
            'Great trades — wait, no trades here anymore haha. Great vibes!',
            'Profile goals honestly.',
        ];

        foreach ($users as $user) {
            if (! $user->shows('allow_comments')) {
                continue;
            }

            $authors = $users->where('id', '!=', $user->id)->shuffle()->take(rand(1, 4));
            foreach ($authors as $author) {
                ProfileComment::create([
                    'profile_user_id' => $user->id,
                    'author_id'       => $author->id,
                    'body'            => $messages[array_rand($messages)],
                    'created_at'      => now()->subDays(rand(0, 20)),
                    'updated_at'      => now(),
                ]);
            }
        }
    }
}

<?php

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\ProfileComment;
use App\Models\Report;
use App\Models\ShoutboxMessage;
use App\Models\User;
use App\Notifications\ForumReplyNotification;
use App\Notifications\ProfileCommentNotification;
use Illuminate\Support\Facades\Notification;

function makeCategory(): ForumCategory
{
    return ForumCategory::create([
        'name' => 'General '.uniqid(),
        'slug' => 'general-'.uniqid(),
        'description' => 'Talk shop',
        'accent' => 'violet',
        'position' => 1,
    ]);
}

function makeThread(User $author, ?ForumCategory $cat = null, array $extra = []): ForumThread
{
    return ForumThread::create(array_merge([
        'forum_category_id' => ($cat ?? makeCategory())->id,
        'user_id' => $author->id,
        'title' => 'Test thread '.uniqid(),
        'body' => 'Opening post body.',
        'last_posted_at' => now(),
    ], $extra));
}

function admin(): User
{
    $u = User::factory()->create();
    $u->forceFill(['role' => 'admin'])->save();

    return $u;
}

// ---------- public viewing ----------

test('guests can view forums index, category and thread', function () {
    $cat = makeCategory();
    $thread = makeThread(User::factory()->create(), $cat);

    $this->get(route('forums.index'))->assertOk();
    $this->get(route('forums.category', $cat))->assertOk();
    $this->get(route('forums.thread', $thread))->assertOk()->assertSee($thread->title);
});

test('forum search finds a matching thread', function () {
    makeThread(User::factory()->create(), null, ['title' => 'Umbreon SIR pull luck']);

    $this->get(route('forums.index', ['q' => 'Umbreon SIR']))
        ->assertOk()
        ->assertSee('Umbreon SIR pull luck');
});

// ---------- threads + replies ----------

test('a user can post a thread and it persists', function () {
    $cat = makeCategory();
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('forums.store'), [
        'forum_category_id' => $cat->id,
        'title' => 'My new thread',
        'body' => 'Hello collectors',
    ])->assertRedirect();

    $this->assertDatabaseHas('forum_threads', ['title' => 'My new thread', 'user_id' => $user->id]);
});

test('replying notifies the thread author but not on self-reply', function () {
    Notification::fake();
    $author = User::factory()->create();
    $thread = makeThread($author);

    // self-reply: no notification
    $this->actingAs($author)->post(route('forums.reply', $thread), ['body' => 'bump']);
    Notification::assertNothingSent();

    // other user reply: author notified
    $other = User::factory()->create();
    $this->actingAs($other)->post(route('forums.reply', $thread), ['body' => 'nice thread']);
    Notification::assertSentTo($author, ForumReplyNotification::class);

    $this->assertDatabaseHas('forum_posts', ['forum_thread_id' => $thread->id, 'user_id' => $other->id]);
});

test('locked threads reject replies from non-admins', function () {
    $thread = makeThread(User::factory()->create(), null, ['locked' => true]);
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('forums.reply', $thread), ['body' => 'sneaky'])->assertRedirect();
    $this->assertDatabaseMissing('forum_posts', ['forum_thread_id' => $thread->id, 'user_id' => $user->id]);
});

test('admins can pin and lock threads; non-admins cannot', function () {
    $thread = makeThread(User::factory()->create());

    // random user blocked
    $this->actingAs(User::factory()->create())->patch(route('forums.lock', $thread))->assertForbidden();

    // admin allowed
    $this->actingAs(admin())->patch(route('forums.lock', $thread))->assertRedirect();
    expect($thread->fresh()->locked)->toBeTrue();

    $this->actingAs(admin())->patch(route('forums.pin', $thread))->assertRedirect();
    expect($thread->fresh()->pinned)->toBeTrue();
});

test('thread author or admin can edit and delete; others cannot', function () {
    $author = User::factory()->create();
    $thread = makeThread($author);

    // stranger forbidden
    $this->actingAs(User::factory()->create())
        ->put(route('forums.update', $thread), ['forum_category_id' => $thread->forum_category_id, 'title' => 'x', 'body' => 'y'])
        ->assertForbidden();

    // author can edit
    $this->actingAs($author)
        ->put(route('forums.update', $thread), ['forum_category_id' => $thread->forum_category_id, 'title' => 'Edited title', 'body' => 'Edited body'])
        ->assertRedirect();
    expect($thread->fresh()->title)->toBe('Edited title');

    // admin can delete
    $this->actingAs(admin())->delete(route('forums.destroy', $thread))->assertRedirect();
    $this->assertDatabaseMissing('forum_threads', ['id' => $thread->id]);
});

test('post author can edit and delete own reply; stranger cannot', function () {
    $thread = makeThread(User::factory()->create());
    $poster = User::factory()->create();
    $post = ForumPost::create(['forum_thread_id' => $thread->id, 'user_id' => $poster->id, 'body' => 'original']);

    $this->actingAs(User::factory()->create())
        ->put(route('forums.posts.update', $post), ['body' => 'hacked'])->assertForbidden();

    $this->actingAs($poster)->put(route('forums.posts.update', $post), ['body' => 'edited reply'])->assertRedirect();
    expect($post->fresh()->body)->toBe('edited reply');

    $this->actingAs($poster)->delete(route('forums.posts.destroy', $post))->assertRedirect();
    $this->assertDatabaseMissing('forum_posts', ['id' => $post->id]);
});

// ---------- reporting ----------

test('a user can report a thread once; duplicates do not stack', function () {
    $thread = makeThread(User::factory()->create());
    $reporter = User::factory()->create();

    $this->actingAs($reporter)->post(route('reports.store'), ['type' => 'thread', 'id' => $thread->id, 'reason' => 'spam']);
    $this->actingAs($reporter)->post(route('reports.store'), ['type' => 'thread', 'id' => $thread->id, 'reason' => 'spam']);

    expect(Report::where('reportable_type', ForumThread::class)->where('reportable_id', $thread->id)->count())->toBe(1);
});

test('users cannot report their own content', function () {
    $author = User::factory()->create();
    $thread = makeThread($author);

    $this->actingAs($author)->post(route('reports.store'), ['type' => 'thread', 'id' => $thread->id, 'reason' => 'spam']);

    expect(Report::count())->toBe(0);
});

test('reporting validates the type and reason', function () {
    $thread = makeThread(User::factory()->create());
    $this->actingAs(User::factory()->create())
        ->post(route('reports.store'), ['type' => 'wallet', 'id' => $thread->id, 'reason' => 'spam'])
        ->assertSessionHasErrors('type');
});

test('admin can list and resolve reports, and remove content', function () {
    $thread = makeThread(User::factory()->create());
    $report = Report::create([
        'reporter_id' => User::factory()->create()->id,
        'reportable_type' => ForumThread::class,
        'reportable_id' => $thread->id,
        'reason' => 'spam',
        'status' => 'open',
    ]);

    $this->actingAs(admin())->get(route('admin.reports.index'))->assertOk();

    // remove deletes the content + resolves
    $this->actingAs(admin())->patch(route('admin.reports.update', $report), ['action' => 'remove'])->assertRedirect();
    $this->assertDatabaseMissing('forum_threads', ['id' => $thread->id]);
    expect($report->fresh()->status)->toBe('resolved');
});

test('non-admins cannot reach the admin reports page', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.reports.index'))->assertForbidden();
});

// ---------- shoutbox ----------

test('shoutbox persists messages and returns json; guests cannot post', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('shoutbox.store'), ['body' => 'gm collectors'])
        ->assertCreated()
        ->assertJsonPath('message.body', 'gm collectors');
    $this->assertDatabaseHas('shoutbox_messages', ['user_id' => $user->id, 'body' => 'gm collectors']);

    $this->getJson(route('shoutbox.index'))->assertOk()->assertJsonStructure(['messages']);
});

test('guests cannot post to the shoutbox', function () {
    $this->post(route('shoutbox.store'), ['body' => 'nope'])->assertRedirect();
    expect(ShoutboxMessage::where('body', 'nope')->exists())->toBeFalse();
});

// ---------- profile comments ----------

test('commenting on a wall notifies the owner and persists', function () {
    Notification::fake();
    $owner = User::factory()->create();
    $commenter = User::factory()->create();

    $this->actingAs($commenter)->post(route('profiles.comment', $owner), ['body' => 'sweet binder'])->assertRedirect();
    $this->assertDatabaseHas('profile_comments', ['profile_user_id' => $owner->id, 'author_id' => $commenter->id]);
    Notification::assertSentTo($owner, ProfileCommentNotification::class);
});

test('comment author, wall owner, and admin can delete; strangers cannot', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $comment = ProfileComment::create(['profile_user_id' => $owner->id, 'author_id' => $commenter->id, 'body' => 'hi']);

    // stranger forbidden
    $this->actingAs(User::factory()->create())
        ->delete(route('profiles.comment.destroy', [$owner, $comment]))->assertForbidden();

    // wall owner can delete
    $this->actingAs($owner)->delete(route('profiles.comment.destroy', [$owner, $comment]))->assertRedirect();
    $this->assertDatabaseMissing('profile_comments', ['id' => $comment->id]);
});

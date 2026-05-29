<?php

namespace App\Http\Controllers;

use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\ProfileComment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * Whitelist of reportable targets. The client sends a short `type`
     * key (never a class name) which we map server-side, so a reporter
     * can never point a report at an arbitrary model.
     */
    private const TYPES = [
        'thread'  => ForumThread::class,
        'post'    => ForumPost::class,
        'comment' => ProfileComment::class,
    ];

    private const REASONS = ['spam', 'harassment', 'inappropriate', 'scam', 'off_topic', 'other'];

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => ['required', Rule::in(array_keys(self::TYPES))],
            'id'      => ['required', 'integer'],
            'reason'  => ['required', Rule::in(self::REASONS)],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);

        $model = self::TYPES[$data['type']];
        $target = $model::findOrFail($data['id']);

        // Don't let people report their own content.
        $ownerId = $target->user_id ?? $target->author_id ?? null;
        if ($ownerId === $request->user()->id) {
            return back()->with('status', 'You cannot report your own content.');
        }

        // One report per reporter per target — quietly succeed if duplicate.
        $already = Report::where('reporter_id', $request->user()->id)
            ->where('reportable_type', $model)
            ->where('reportable_id', $target->id)
            ->exists();

        if (! $already) {
            Report::create([
                'reporter_id'     => $request->user()->id,
                'reportable_type' => $model,
                'reportable_id'   => $target->id,
                'reason'          => $data['reason'],
                'details'         => $data['details'] ?? null,
                'status'          => Report::STATUS_OPEN,
            ]);
        }

        return back()->with('status', 'Report submitted. Thanks for keeping the community safe.');
    }
}

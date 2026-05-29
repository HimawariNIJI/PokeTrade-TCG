<x-admin-layout heading="Reports" eyebrow="Community moderation">

{{-- Status filter tabs --}}
<div class="mb-5 flex flex-wrap gap-2">
    @php
        $tabs = [
            'open'      => 'Open (' . $counts['open'] . ')',
            'resolved'  => 'Resolved (' . $counts['resolved'] . ')',
            'dismissed' => 'Dismissed (' . $counts['dismissed'] . ')',
            'all'       => 'All',
        ];
    @endphp
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.reports.index', ['status' => $key]) }}"
           class="rounded-full px-4 py-2 text-sm font-bold transition {{ $status === $key ? 'bg-ink-900 text-white' : 'border border-ink-200 text-ink-700 hover:bg-ink-100' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if($reports->isEmpty())
    <x-empty-state icon="⚑" title="No reports here"
        message="Nothing flagged in this view. When members report content, it lands here for review." />
@else
    <div class="space-y-4">
        @foreach($reports as $report)
            @php
                $r = $report->reportable;
                $targetUrl = null;
                $preview = 'Content already removed';
                $typeLabel = class_basename($report->reportable_type);
                if ($r instanceof \App\Models\ForumThread) {
                    $targetUrl = route('forums.thread', $r); $preview = $r->title; $typeLabel = 'Thread';
                } elseif ($r instanceof \App\Models\ForumPost) {
                    $targetUrl = route('forums.thread', $r->forum_thread_id); $preview = Str::limit($r->body, 120); $typeLabel = 'Reply';
                } elseif ($r instanceof \App\Models\ProfileComment) {
                    $targetUrl = route('profiles.show', $r->profile_user_id); $preview = Str::limit($r->body, 120); $typeLabel = 'Wall comment';
                }
                $badge = [
                    'open' => 'bg-rose-100 text-rose-700',
                    'resolved' => 'bg-emerald-100 text-emerald-700',
                    'dismissed' => 'bg-ink-100 text-ink-600',
                ][$report->status] ?? 'bg-ink-100 text-ink-600';
            @endphp

            <div class="rounded-3xl border border-ink-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-ink-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-ink-700">{{ $typeLabel }}</span>
                            <span class="rounded-full bg-prism-violet/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-prism-violet">{{ ucfirst(str_replace('_', ' ', $report->reason)) }}</span>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest {{ $badge }}">{{ $report->status }}</span>
                        </div>

                        <p class="mt-3 line-clamp-2 text-sm font-semibold text-ink-900">{{ $preview }}</p>
                        @if($report->details)
                            <p class="mt-1 line-clamp-2 text-xs text-ink-500">“{{ $report->details }}”</p>
                        @endif

                        <p class="mt-3 text-[11px] text-ink-500">
                            Reported by <span class="font-semibold text-ink-700">{{ $report->reporter?->name ?? 'Unknown' }}</span>
                            · {{ $report->created_at->diffForHumans() }}
                            @if($targetUrl)
                                · <a href="{{ $targetUrl }}" target="_blank" class="font-semibold text-prism-violet hover:underline">View content ↗</a>
                            @endif
                            @if($report->handler)
                                · handled by {{ $report->handler->name }} {{ optional($report->handled_at)->diffForHumans() }}
                            @endif
                        </p>
                    </div>

                    @if($report->status === 'open')
                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="dismiss">
                                <button class="rounded-full border border-ink-200 px-3 py-1.5 text-xs font-bold text-ink-700 hover:bg-ink-100">Dismiss</button>
                            </form>
                            <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="resolve">
                                <button class="rounded-full border border-emerald-200 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50">Resolve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.reports.update', $report) }}"
                                  onsubmit="return confirm('Delete the reported content? This cannot be undone.')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="remove">
                                <button class="rounded-full bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Remove content</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">{{ $reports->links() }}</div>
@endif

</x-admin-layout>

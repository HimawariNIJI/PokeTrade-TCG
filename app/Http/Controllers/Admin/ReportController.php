<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', Report::STATUS_OPEN);
        if (! in_array($status, [Report::STATUS_OPEN, Report::STATUS_RESOLVED, Report::STATUS_DISMISSED, 'all'], true)) {
            $status = Report::STATUS_OPEN;
        }

        $reports = Report::query()
            ->with(['reporter', 'handler', 'reportable'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'open'      => Report::where('status', Report::STATUS_OPEN)->count(),
            'resolved'  => Report::where('status', Report::STATUS_RESOLVED)->count(),
            'dismissed' => Report::where('status', Report::STATUS_DISMISSED)->count(),
        ];

        return view('admin.reports.index', compact('reports', 'status', 'counts'));
    }

    /**
     * Resolve, dismiss, or remove the reported content.
     */
    public function update(Request $request, Report $report)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['resolve', 'dismiss', 'remove'])],
        ]);

        if ($data['action'] === 'remove') {
            // Delete the offending content, then mark the report resolved.
            $report->reportable?->delete();
            $status = Report::STATUS_RESOLVED;
            $flash = 'Content removed and report resolved.';
        } elseif ($data['action'] === 'resolve') {
            $status = Report::STATUS_RESOLVED;
            $flash = 'Report marked resolved.';
        } else {
            $status = Report::STATUS_DISMISSED;
            $flash = 'Report dismissed.';
        }

        $report->update([
            'status'     => $status,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('status', $flash);
    }
}

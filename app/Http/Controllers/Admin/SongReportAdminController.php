<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SongReport;

class SongReportAdminController extends Controller
{
    public function index()
    {
        $reports = SongReport::with([
                'song',
                'user',
            ])
            ->latest()
            ->paginate(20);

        return view(
            'admin.song-reports.index',
            compact('reports')
        );
    }

    public function review(SongReport $report)
    {
        $report->update([
            'status' => 'reviewed',
        ]);

        return back()->with(
            'success',
            'Report marked as reviewed.'
        );
    }

    public function dismiss(SongReport $report)
    {
        $report->update([
            'status' => 'dismissed',
        ]);

        return back()->with(
            'success',
            'Report dismissed.'
        );
    }

    
}
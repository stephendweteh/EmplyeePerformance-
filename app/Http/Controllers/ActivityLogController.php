<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $logs = ActivityLog::with('actor')
            ->when($user->isEmployee(), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('actor_id', $user->id)
                        ->orWhereJsonContains('meta->employee_id', $user->id);
                });
            })
            ->latest()
            ->paginate(30);

        return view('activity-logs.index', [
            'logs' => $logs,
        ]);
    }
}

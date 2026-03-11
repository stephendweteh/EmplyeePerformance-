<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EmployeeUpdate;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $teamIds = $user->teams()->pluck('teams.id')->all();

        $updates = EmployeeUpdate::with(['reviews.reviewer', 'attachments'])
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->paginate(10);

        $announcements = Announcement::with(['author', 'attachments', 'reads' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
            ->where(function ($query) {
                $query->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->where(function ($query) use ($user, $teamIds) {
                $query->whereDoesntHave('targets')
                    ->orWhereHas('targets', function ($targetQuery) use ($user, $teamIds) {
                        $targetQuery
                            ->where('target_type', 'all')
                            ->orWhere(function ($teamQuery) use ($teamIds) {
                                $teamQuery
                                    ->where('target_type', 'team')
                                    ->whereIn('target_id', $teamIds);
                            })
                            ->orWhere(function ($userQuery) use ($user) {
                                $userQuery
                                    ->where('target_type', 'user')
                                    ->where('target_id', $user->id);
                            });
                    });
            })
            ->latest()
            ->limit(10)
            ->get();

        return view('employee.dashboard', [
            'updates' => $updates,
            'announcements' => $announcements,
        ]);
    }
}

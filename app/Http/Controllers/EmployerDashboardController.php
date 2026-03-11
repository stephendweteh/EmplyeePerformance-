<?php

namespace App\Http\Controllers;

use App\Models\EmployeeUpdate;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->string('date')->toString() ?: now()->toDateString();
        $teamId = $request->integer('team_id');

        $updates = EmployeeUpdate::with(['user.team', 'reviews.reviewer', 'attachments'])
            ->whereDate('date', $date)
            ->when($teamId, function ($query, $teamId) {
                $query->whereHas('user.teams', fn ($teamQuery) => $teamQuery->where('teams.id', $teamId));
            })
            ->orderByDesc('created_at')
            ->get();

        return view('employer.dashboard', [
            'selectedDate' => $date,
            'selectedTeamId' => $teamId,
            'teams' => Team::orderBy('name')->get(),
            'updates' => $updates,
        ]);
    }
}

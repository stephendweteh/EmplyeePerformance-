<?php

namespace App\Http\Controllers;

use App\Models\EmployeeUpdate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private function filteredUpdates(Request $request)
    {
        $date = $request->string('date')->toString() ?: now()->toDateString();
        $teamId = $request->integer('team_id');

        return EmployeeUpdate::with(['user.team', 'user.teams', 'reviews.reviewer'])
            ->whereDate('date', $date)
            ->when($teamId, function ($query, $teamId) {
                $query->whereHas('user.teams', fn ($teamQuery) => $teamQuery->where('teams.id', $teamId));
            })
            ->orderBy('user_id')
            ->get();
    }

    public function updatesCsv(Request $request): StreamedResponse
    {
        $updates = $this->filteredUpdates($request);
        $date = $request->string('date')->toString() ?: now()->toDateString();

        return response()->streamDownload(function () use ($updates): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Employee', 'Primary Team', 'Teams', 'Wins', 'Business Impact', 'Blockers', 'Rating', 'Review Status', 'Review Comment']);

            foreach ($updates as $update) {
                $review = $update->reviews->first();
                fputcsv($handle, [
                    $update->date->toDateString(),
                    $update->user->name,
                    $update->user->team?->name,
                    $update->user->teams->pluck('name')->implode('; '),
                    $update->wins,
                    $update->business_impact,
                    $update->blockers,
                    $review?->rating,
                    $review?->status,
                    $review?->comment,
                ]);
            }

            fclose($handle);
        }, 'employee-updates-'.$date.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function updatesPdf(Request $request)
    {
        $updates = $this->filteredUpdates($request);
        $date = $request->string('date')->toString() ?: now()->toDateString();

        $pdf = Pdf::loadView('exports.employee-updates-pdf', [
            'updates' => $updates,
            'selectedDate' => $date,
        ]);

        return $pdf->download('employee-updates-'.$date.'.pdf');
    }
}

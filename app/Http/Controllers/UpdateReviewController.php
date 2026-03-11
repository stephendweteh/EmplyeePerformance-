<?php

namespace App\Http\Controllers;

use App\Models\EmployeeUpdate;
use App\Models\UpdateReview;
use App\Notifications\UpdateReviewedNotification;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class UpdateReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_update_id' => ['required', 'exists:employee_updates,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
            'comment' => ['nullable', 'string'],
            'status' => ['required', 'in:reviewed,needs_follow_up'],
        ]);

        $review = UpdateReview::updateOrCreate(
            [
                'employee_update_id' => $validated['employee_update_id'],
                'reviewer_id' => auth()->id(),
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'status' => $validated['status'],
            ]
        );

        $update = EmployeeUpdate::with('user')->findOrFail($validated['employee_update_id']);
        $update->user->notify(new UpdateReviewedNotification($review));

        ActivityLogger::log(auth()->id(), 'update_review.saved', $review, [
            'employee_id' => $update->user_id,
            'rating' => $review->rating,
        ]);

        return back()->with('success', 'Review saved.');
    }

    public function update(Request $request, UpdateReview $updateReview)
    {
        if ($updateReview->reviewer_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
            'comment' => ['nullable', 'string'],
            'status' => ['required', 'in:reviewed,needs_follow_up'],
        ]);

        $updateReview->update($validated);

        $updateReview->load('employeeUpdate.user');
        $updateReview->employeeUpdate->user->notify(new UpdateReviewedNotification($updateReview));

        ActivityLogger::log(auth()->id(), 'update_review.updated', $updateReview, [
            'employee_id' => $updateReview->employeeUpdate->user_id,
            'rating' => $updateReview->rating,
        ]);

        return back()->with('success', 'Review updated.');
    }
}

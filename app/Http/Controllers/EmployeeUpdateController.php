<?php

namespace App\Http\Controllers;

use App\Models\EmployeeUpdate;
use App\Models\EmployeeUpdateAttachment;
use App\Models\User;
use App\Notifications\EmployeeUpdateSubmittedNotification;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class EmployeeUpdateController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', EmployeeUpdate::class);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'wins' => ['required', 'string'],
            'business_impact' => ['nullable', 'string'],
            'blockers' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,png,jpg,jpeg,webp,txt,csv,xlsx,xls'],
        ]);

        $update = EmployeeUpdate::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'wins' => $validated['wins'],
            'business_impact' => $validated['business_impact'] ?? null,
            'blockers' => $validated['blockers'] ?? null,
            'tags' => isset($validated['tags'])
                ? collect(explode(',', $validated['tags']))->map(fn ($tag) => trim($tag))->filter()->values()->all()
                : null,
        ]);

        ActivityLogger::log(auth()->id(), 'employee_update.created', $update, [
            'date' => $update->date->toDateString(),
        ]);

        foreach ($request->file('attachments', []) as $attachment) {
            $path = $attachment->store('employee-update-attachments', 'public');

            EmployeeUpdateAttachment::create([
                'employee_update_id' => $update->id,
                'file_name' => $attachment->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $attachment->getClientMimeType(),
                'size_bytes' => $attachment->getSize(),
            ]);
        }

        $update->load('user.team');
        $employerUsers = User::query()
            ->whereIn('role', ['employer', 'admin', 'super_admin'])
            ->where('id', '!=', auth()->id())
            ->get();

        Notification::send($employerUsers, new EmployeeUpdateSubmittedNotification($update));

        return back()->with('success', 'Daily update saved.');
    }

    public function update(Request $request, EmployeeUpdate $employeeUpdate)
    {
        $this->authorize('update', $employeeUpdate);

        $validated = $request->validate([
            'wins' => ['required', 'string'],
            'business_impact' => ['nullable', 'string'],
            'blockers' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],
        ]);

        $employeeUpdate->update([
            'wins' => $validated['wins'],
            'business_impact' => $validated['business_impact'] ?? null,
            'blockers' => $validated['blockers'] ?? null,
            'tags' => isset($validated['tags'])
                ? collect(explode(',', $validated['tags']))->map(fn ($tag) => trim($tag))->filter()->values()->all()
                : null,
        ]);

        ActivityLogger::log(auth()->id(), 'employee_update.updated', $employeeUpdate);

        return back()->with('success', 'Daily update updated.');
    }
}

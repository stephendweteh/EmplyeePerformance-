<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\AnnouncementRead;
use App\Models\AnnouncementTarget;
use App\Models\Team;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $teamIds = $user->teams()->pluck('teams.id')->all();

        $announcements = Announcement::with(['author', 'targets', 'attachments', 'reads' => function ($query) use ($user) {
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
            ->paginate(12);

        return view('announcements.index', [
            'announcements' => $announcements,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('announcements.create', [
            'teams' => Team::orderBy('name')->get(),
            'employees' => User::where('role', 'employee')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Announcement::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'target_type' => ['required', 'in:all,team,user'],
            'team_target_id' => [
                Rule::requiredIf($request->input('target_type') === 'team'),
                Rule::prohibitedIf($request->input('target_type') !== 'team'),
                'nullable',
                'integer',
                Rule::exists('teams', 'id'),
            ],
            'user_target_id' => [
                Rule::requiredIf($request->input('target_type') === 'user'),
                Rule::prohibitedIf($request->input('target_type') !== 'user'),
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'employee')),
            ],
            'publish_at' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,png,jpg,jpeg,webp,txt,csv'],
            'picture_adverts' => ['nullable', 'array', 'max:8'],
            'picture_adverts.*' => ['image', 'max:10240', 'mimes:png,jpg,jpeg,webp,gif'],
        ]);

        $targetId = null;
        if ($validated['target_type'] === 'team') {
            $targetId = $validated['team_target_id'] ?? null;
        }

        if ($validated['target_type'] === 'user') {
            $targetId = $validated['user_target_id'] ?? null;
        }

        $announcement = Announcement::create([
            'author_id' => auth()->id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'publish_at' => $validated['publish_at'] ?? null,
        ]);

        AnnouncementTarget::create([
            'announcement_id' => $announcement->id,
            'target_type' => $validated['target_type'],
            'target_id' => in_array($validated['target_type'], ['team', 'user'], true) ? $targetId : null,
        ]);

        foreach ($request->file('attachments', []) as $attachment) {
            $path = $attachment->store('announcement-attachments', 'public');

            AnnouncementAttachment::create([
                'announcement_id' => $announcement->id,
                'file_name' => $attachment->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $attachment->getClientMimeType(),
                'size_bytes' => $attachment->getSize(),
            ]);
        }

        foreach ($request->file('picture_adverts', []) as $pictureAdvert) {
            $path = $pictureAdvert->store('announcement-attachments', 'public');

            AnnouncementAttachment::create([
                'announcement_id' => $announcement->id,
                'file_name' => $pictureAdvert->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $pictureAdvert->getClientMimeType(),
                'size_bytes' => $pictureAdvert->getSize(),
            ]);
        }

        $recipientsQuery = User::where('role', 'employee');

        if ($validated['target_type'] === 'team') {
            $recipientsQuery->whereHas('teams', fn ($query) => $query->where('teams.id', $targetId));
        }

        if ($validated['target_type'] === 'user') {
            $recipientsQuery->whereKey($targetId);
        }

        $recipients = $recipientsQuery->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new AnnouncementPublishedNotification($announcement));
        }

        ActivityLogger::log(auth()->id(), 'announcement.published', $announcement, [
            'target_type' => $validated['target_type'],
            'recipient_count' => $recipients->count(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Live update published.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        $announcement->load(['targets', 'attachments']);
        $target = $announcement->targets->first();

        return view('announcements.edit', [
            'announcement' => $announcement,
            'targetType' => $target?->target_type ?? 'all',
            'teamTargetId' => $target?->target_type === 'team' ? $target->target_id : null,
            'userTargetId' => $target?->target_type === 'user' ? $target->target_id : null,
            'teams' => Team::orderBy('name')->get(),
            'employees' => User::where('role', 'employee')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'target_type' => ['required', 'in:all,team,user'],
            'team_target_id' => [
                Rule::requiredIf($request->input('target_type') === 'team'),
                Rule::prohibitedIf($request->input('target_type') !== 'team'),
                'nullable',
                'integer',
                Rule::exists('teams', 'id'),
            ],
            'user_target_id' => [
                Rule::requiredIf($request->input('target_type') === 'user'),
                Rule::prohibitedIf($request->input('target_type') !== 'user'),
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'employee')),
            ],
            'publish_at' => ['nullable', 'date'],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => [
                'integer',
                Rule::exists('announcement_attachments', 'id')->where(fn ($query) => $query->where('announcement_id', $announcement->id)),
            ],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,png,jpg,jpeg,webp,txt,csv'],
            'picture_adverts' => ['nullable', 'array', 'max:8'],
            'picture_adverts.*' => ['image', 'max:10240', 'mimes:png,jpg,jpeg,webp,gif'],
        ]);

        $targetId = null;
        if ($validated['target_type'] === 'team') {
            $targetId = $validated['team_target_id'] ?? null;
        }

        if ($validated['target_type'] === 'user') {
            $targetId = $validated['user_target_id'] ?? null;
        }

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'publish_at' => $validated['publish_at'] ?? null,
        ]);

        $announcement->targets()->delete();
        AnnouncementTarget::create([
            'announcement_id' => $announcement->id,
            'target_type' => $validated['target_type'],
            'target_id' => in_array($validated['target_type'], ['team', 'user'], true) ? $targetId : null,
        ]);

        foreach ($validated['remove_attachment_ids'] ?? [] as $attachmentId) {
            $attachment = AnnouncementAttachment::query()
                ->where('announcement_id', $announcement->id)
                ->whereKey($attachmentId)
                ->first();

            if ($attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            }
        }

        foreach ($request->file('attachments', []) as $attachment) {
            $path = $attachment->store('announcement-attachments', 'public');

            AnnouncementAttachment::create([
                'announcement_id' => $announcement->id,
                'file_name' => $attachment->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $attachment->getClientMimeType(),
                'size_bytes' => $attachment->getSize(),
            ]);
        }

        foreach ($request->file('picture_adverts', []) as $pictureAdvert) {
            $path = $pictureAdvert->store('announcement-attachments', 'public');

            AnnouncementAttachment::create([
                'announcement_id' => $announcement->id,
                'file_name' => $pictureAdvert->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $pictureAdvert->getClientMimeType(),
                'size_bytes' => $pictureAdvert->getSize(),
            ]);
        }

        ActivityLogger::log(auth()->id(), 'announcement.updated', $announcement, [
            'target_type' => $validated['target_type'],
        ]);

        return redirect()->route('announcements.index')->with('success', 'Live update updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->load('attachments');

        foreach ($announcement->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $announcement->delete();

        ActivityLogger::log(auth()->id(), 'announcement.deleted', $announcement);

        return redirect()->route('announcements.index')->with('success', 'Live update removed.');
    }

    public function markRead(Announcement $announcement)
    {
        $read = AnnouncementRead::updateOrCreate(
            [
                'announcement_id' => $announcement->id,
                'user_id' => auth()->id(),
            ],
            [
                'read_at' => now(),
            ]
        );

        ActivityLogger::log(auth()->id(), 'announcement.read', $announcement, [
            'read_record_id' => $read->id,
        ]);

        return back();
    }
}

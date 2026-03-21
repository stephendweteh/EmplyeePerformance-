<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Edit Live Update</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="md-card">
                <div class="md-card-body">
                    @if ($errors->any())
                        <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 px-3 py-2 text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-4" enctype="multipart/form-data" x-data="{ type: '{{ old('target_type', $targetType) }}' }">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="text-sm text-slate-700">Title</label>
                            <input type="text" name="title" value="{{ old('title', $announcement->title) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Body</label>
                            <textarea name="body" rows="5" class="mt-1 w-full rounded-lg border-slate-300" required>{{ old('body', $announcement->body) }}</textarea>
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Target Audience</label>
                            <select name="target_type" x-model="type" class="mt-1 w-full rounded-lg border-slate-300" required>
                                <option value="all">All Employees</option>
                                <option value="team">Team</option>
                                <option value="user">Specific Employee</option>
                            </select>
                        </div>
                        <div x-show="type === 'team'" x-cloak>
                            <label class="text-sm text-slate-700">Choose Team</label>
                            <select name="team_target_id" x-bind:disabled="type !== 'team'" class="mt-1 w-full rounded-lg border-slate-300">
                                <option value="">Select a team</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" @selected((string) old('team_target_id', $teamTargetId) === (string) $team->id)>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="type === 'user'" x-cloak>
                            <label class="text-sm text-slate-700">Choose Employee</label>
                            <select name="user_target_id" x-bind:disabled="type !== 'user'" class="mt-1 w-full rounded-lg border-slate-300">
                                <option value="">Select an employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected((string) old('user_target_id', $userTargetId) === (string) $employee->id)>{{ $employee->name }} ({{ $employee->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Publish At (optional)</label>
                            <input
                                type="datetime-local"
                                name="publish_at"
                                value="{{ old('publish_at', optional($announcement->publish_at)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 w-full rounded-lg border-slate-300"
                            >
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 space-y-3">
                            <p class="text-sm font-medium text-slate-800">Files &amp; images</p>
                            <p class="text-xs text-slate-500">Remove existing files with the checkboxes, or add new ones below. Picture adverts are shown as images; other types appear as download links on the live update.</p>

                            @if ($announcement->attachments->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach ($announcement->attachments->sortBy('id') as $attachment)
                                        @php($isImage = $attachment->mime_type && str_starts_with($attachment->mime_type, 'image/'))
                                        <div class="flex flex-wrap items-start gap-3 rounded-lg border border-slate-200 bg-white p-3">
                                            @if ($isImage)
                                                <a href="{{ $attachment->fileUrl() }}" target="_blank" rel="noopener noreferrer" class="shrink-0">
                                                    <img src="{{ $attachment->fileUrl() }}" alt="" class="h-20 w-28 rounded-md object-cover border border-slate-200">
                                                </a>
                                            @else
                                                <div class="flex h-20 w-28 shrink-0 items-center justify-center rounded-md border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-500 px-1 text-center">
                                                    {{ strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION) ?: 'file') }}
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-slate-800 truncate">{{ $attachment->file_name }}</p>
                                                <p class="text-xs text-slate-500">{{ $attachment->mime_type ?? 'unknown type' }}</p>
                                                <a href="{{ $attachment->fileUrl() }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-block text-xs text-sky-700 hover:underline">Open</a>
                                            </div>
                                            <label class="flex items-center gap-2 text-sm text-rose-700 cursor-pointer shrink-0">
                                                <input type="checkbox" name="remove_attachment_ids[]" value="{{ $attachment->id }}" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                                <span>Remove</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-500">No files on this live update yet.</p>
                            @endif

                            <div class="border-t border-slate-200 pt-3 space-y-3">
                                <div>
                                    <label class="text-sm text-slate-700">Add attachments (optional, up to 5 per save)</label>
                                    <input type="file" name="attachments[]" class="mt-1 w-full rounded-lg border-slate-300 bg-white" multiple>
                                    <p class="text-xs text-slate-500 mt-1">PDF, DOC, DOCX, PNG, JPG, WEBP, TXT, CSV — max 10MB each.</p>
                                </div>
                                <div>
                                    <label class="text-sm text-slate-700">Add picture adverts (optional)</label>
                                    <input type="file" name="picture_adverts[]" class="mt-1 w-full rounded-lg border-slate-300 bg-white" multiple accept="image/png,image/jpeg,image/webp,image/gif">
                                    <p class="text-xs text-slate-500 mt-1">PNG, JPG, JPEG, WEBP, GIF — max 10MB each.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <button class="rounded-lg bg-sky-600 text-white px-4 py-2 font-medium hover:bg-sky-700">Save Changes</button>
                            <a href="{{ route('announcements.index') }}" class="text-sm text-slate-600 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

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

                    <form method="POST" action="{{ route('announcements.update', $announcement) }}" class="space-y-4" x-data="{ type: '{{ old('target_type', $targetType) }}' }">
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

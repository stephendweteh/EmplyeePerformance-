<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Teams</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid lg:grid-cols-3 gap-6">
            <div class="md-card lg:col-span-1">
                <div class="md-card-body">
                    <h3 class="font-medium text-slate-800">Create Team</h3>
                    <form method="POST" action="{{ route('teams.store') }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="text" name="name" class="w-full rounded-lg border-slate-300" placeholder="Team name" required>
                        <textarea name="description" rows="3" class="w-full rounded-lg border-slate-300" placeholder="Description"></textarea>
                        <button class="w-full rounded-lg bg-slate-900 text-white px-3 py-2">Create</button>
                    </form>

                    <h3 class="font-medium text-slate-800 mt-8">Assign Employee</h3>
                    <form method="POST" action="{{ route('teams.assign') }}" class="mt-4 space-y-3">
                        @csrf
                        <select name="user_id" class="w-full rounded-lg border-slate-300" required>
                            <option value="">Select employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->team?->name ?? 'No team' }})</option>
                            @endforeach
                        </select>
                        <select name="team_id" class="w-full rounded-lg border-slate-300" required>
                            <option value="">Select team</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="set_primary" value="1" checked class="rounded border-slate-300">
                            Set as primary team
                        </label>
                        <button class="w-full rounded-lg bg-sky-600 text-white px-3 py-2">Assign</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <div class="md-card">
                    <div class="md-card-body">
                        <h3 class="font-medium text-slate-800">Employee Team Memberships</h3>
                        <div class="mt-3 space-y-3">
                            @foreach ($employees as $employee)
                                <div class="rounded-xl border border-slate-200 p-3">
                                    <p class="font-medium text-slate-800">{{ $employee->name }}</p>
                                    <p class="text-xs text-slate-500">Primary: {{ $employee->team?->name ?? 'Not set' }}</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($employee->teams as $membership)
                                            <div class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 text-xs">
                                                <span>{{ $membership->name }}</span>
                                                <form method="POST" action="{{ route('teams.set-primary') }}">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                                    <input type="hidden" name="team_id" value="{{ $membership->id }}">
                                                    <button class="text-indigo-700">Primary</button>
                                                </form>
                                                <form method="POST" action="{{ route('teams.remove-member') }}">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                                    <input type="hidden" name="team_id" value="{{ $membership->id }}">
                                                    <button class="text-rose-700">Remove</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @forelse ($teams as $team)
                    <div class="md-card">
                        <div class="md-card-body">
                            <form method="POST" action="{{ route('teams.update', $team) }}" class="grid md:grid-cols-4 gap-3 items-end">
                                @csrf
                                @method('PUT')
                                <div class="md:col-span-1">
                                    <label class="text-xs text-slate-500">Name</label>
                                    <input type="text" name="name" value="{{ $team->name }}" class="w-full rounded-lg border-slate-300" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs text-slate-500">Description</label>
                                    <input type="text" name="description" value="{{ $team->description }}" class="w-full rounded-lg border-slate-300">
                                </div>
                                <button class="rounded-lg bg-emerald-600 text-white px-3 py-2">Update</button>
                            </form>
                            <p class="text-xs text-slate-500 mt-2">{{ $team->members_count }} member(s)</p>
                        </div>
                    </div>
                @empty
                    <div class="md-card">
                        <div class="md-card-body text-slate-500">No teams created yet.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

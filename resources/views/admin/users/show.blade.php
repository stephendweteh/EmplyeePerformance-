<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">User Profile</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="md-card">
                <div class="md-card-body">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-800">{{ $user->name }}</h3>
                            <p class="text-sm text-slate-500">{{ $user->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs rounded-full bg-slate-100 text-slate-700 px-3 py-1 inline-block">Role: {{ $user->role }}</p>
                            <p class="text-xs text-slate-500 mt-2">Primary Team: {{ $user->team?->name ?? 'Not set' }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm font-medium text-slate-700">Team Memberships</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @forelse ($user->teams as $team)
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">{{ $team->name }}</span>
                            @empty
                                <span class="text-sm text-slate-500">No teams assigned</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm font-medium text-slate-700">Effective Privileges</p>
                        <div class="mt-2 grid sm:grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach ($privileges as $privilege)
                                <span class="text-xs px-2 py-1 rounded-full {{ $user->hasPrivilege($privilege) ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $privilege }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <div class="md-card">
                    <div class="md-card-body">
                        <h4 class="font-medium text-slate-800">Recent Employee Updates</h4>
                        <div class="mt-3 space-y-3">
                            @forelse ($user->updates->sortByDesc('date')->take(8) as $update)
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <p class="text-xs text-slate-500">{{ $update->date->toFormattedDateString() }}</p>
                                    <p class="text-sm text-slate-700 mt-1">{{ $update->wins }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No updates found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="md-card">
                    <div class="md-card-body">
                        <h4 class="font-medium text-slate-800">Recent Reviews Given</h4>
                        <div class="mt-3 space-y-3">
                            @forelse ($user->reviewsGiven->sortByDesc('created_at')->take(8) as $review)
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <p class="text-xs text-slate-500">For {{ $review->employeeUpdate?->user?->name ?? 'Unknown user' }}</p>
                                    <p class="text-sm text-slate-700 mt-1">Rating: {{ $review->rating }}/10</p>
                                    <p class="text-xs text-slate-500">{{ $review->comment ?: 'No comment' }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No reviews found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="md-card">
                <div class="md-card-body">
                    <h4 class="font-medium text-slate-800">Recent Activity Logs</h4>
                    <div class="mt-3 space-y-2">
                        @forelse ($user->activityLogs->sortByDesc('created_at')->take(12) as $log)
                            <div class="rounded-lg border border-slate-200 p-3 flex items-center justify-between gap-3">
                                <p class="text-sm text-slate-700">{{ $log->action }}</p>
                                <p class="text-xs text-slate-500">{{ $log->created_at->toDayDateTimeString() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No activity found.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-sky-700 hover:underline">Back to Users</a>
            </div>
        </div>
    </div>
</x-app-layout>

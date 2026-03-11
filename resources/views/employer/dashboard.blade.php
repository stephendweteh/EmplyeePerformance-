<x-app-layout>
    @once
        <style>
            .rating-stars {
                display: inline-flex;
                flex-direction: row-reverse;
                gap: 0.15rem;
            }

            .rating-stars input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .rating-stars label {
                cursor: pointer;
                color: #cbd5e1;
                font-size: 1.15rem;
                line-height: 1;
                transition: color 0.15s ease;
            }

            .rating-stars label:hover,
            .rating-stars label:hover ~ label,
            .rating-stars input:checked ~ label {
                color: #f59e0b;
            }
        </style>
    @endonce

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Employer Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="md-card">
                <div class="md-card-body">
                    <form method="GET" action="{{ route('employer.dashboard') }}" class="grid sm:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="text-sm text-slate-700">Date</label>
                            <input type="date" name="date" value="{{ $selectedDate }}" class="mt-1 w-full rounded-lg border-slate-300">
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Team</label>
                            <select name="team_id" class="mt-1 w-full rounded-lg border-slate-300">
                                <option value="">All Teams</option>
                                @foreach ($teams as $team)
                                    <option value="{{ $team->id }}" @selected($selectedTeamId === $team->id)>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="rounded-lg bg-slate-900 text-white px-4 py-2 font-medium">Apply Filters</button>
                    </form>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('exports.updates.csv', ['date' => $selectedDate, 'team_id' => $selectedTeamId]) }}" class="rounded-lg bg-emerald-600 text-white px-3 py-2 text-sm">Export CSV</a>
                        <a href="{{ route('exports.updates.pdf', ['date' => $selectedDate, 'team_id' => $selectedTeamId]) }}" class="rounded-lg bg-indigo-600 text-white px-3 py-2 text-sm">Export PDF</a>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($updates as $update)
                    <div class="md-card">
                        <div class="md-card-body">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $update->user->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $update->user->team?->name ?? 'No team' }} • {{ $update->date->toFormattedDateString() }}</p>
                                </div>
                                <a href="{{ route('employees.show', $update->user) }}" class="text-sm text-sky-700 hover:underline">View Profile</a>
                            </div>

                            <div class="mt-4 grid md:grid-cols-3 gap-4 text-sm">
                                <div class="md:col-span-2">
                                    <p class="font-medium text-slate-700">Wins</p>
                                    <p class="text-slate-700 whitespace-pre-wrap">{{ $update->wins }}</p>
                                    @if ($update->attachments->isNotEmpty())
                                        <p class="mt-3 font-medium text-slate-700">Attachments</p>
                                        <div class="mt-1 flex flex-wrap gap-2">
                                            @foreach ($update->attachments as $attachment)
                                                <a href="{{ $attachment->fileUrl() }}" target="_blank" class="text-xs px-2 py-1 rounded-full bg-sky-100 text-sky-700 hover:bg-sky-200">
                                                    {{ $attachment->file_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($update->business_impact)
                                        <p class="mt-3 font-medium text-slate-700">Business Impact</p>
                                        <p class="text-slate-700 whitespace-pre-wrap">{{ $update->business_impact }}</p>
                                    @endif
                                    @if ($update->blockers)
                                        <p class="mt-3 font-medium text-slate-700">Blockers</p>
                                        <p class="text-slate-700 whitespace-pre-wrap">{{ $update->blockers }}</p>
                                    @endif
                                </div>
                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                                    @php($myReview = $update->reviews->firstWhere('reviewer_id', auth()->id()))
                                    @php($selectedRating = (int) old('rating', $myReview?->rating ?? 0))
                                    <p class="font-medium text-slate-800">Rate (1-10)</p>
                                    <form method="POST" action="{{ route('update-reviews.store') }}" class="mt-2 space-y-2">
                                        @csrf
                                        <input type="hidden" name="employee_update_id" value="{{ $update->id }}">
                                        <div class="rating-stars" aria-label="Rating stars">
                                            @for ($star = 10; $star >= 1; $star--)
                                                <input
                                                    id="rating-{{ $update->id }}-{{ $star }}"
                                                    type="radio"
                                                    name="rating"
                                                    value="{{ $star }}"
                                                    @checked($selectedRating === $star)
                                                    required
                                                >
                                                <label for="rating-{{ $update->id }}-{{ $star }}" title="{{ $star }} out of 10">★</label>
                                            @endfor
                                        </div>
                                        <p class="text-xs text-slate-500">{{ $selectedRating > 0 ? 'Selected: '.$selectedRating.'/10' : 'Select a star rating' }}</p>
                                        <select name="status" class="w-full rounded-lg border-slate-300">
                                            <option value="reviewed" @selected($myReview?->status === 'reviewed')>Reviewed</option>
                                            <option value="needs_follow_up" @selected($myReview?->status === 'needs_follow_up')>Needs follow-up</option>
                                        </select>
                                        <textarea name="comment" rows="2" class="w-full rounded-lg border-slate-300" placeholder="Feedback">{{ $myReview?->comment }}</textarea>
                                        <button class="w-full rounded-lg bg-emerald-600 text-white px-3 py-2">Save Review</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="md-card">
                        <div class="md-card-body text-slate-500">No employee updates found for this filter.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

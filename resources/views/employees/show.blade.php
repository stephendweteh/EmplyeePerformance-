<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Employee Profile</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="md-card">
                <div class="md-card-body">
                    <h3 class="text-xl font-semibold text-slate-800">{{ $employee->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $employee->email }} • {{ $employee->team?->name ?? 'No team assigned' }}</p>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($updates as $update)
                    <div class="md-card">
                        <div class="md-card-body">
                            <div class="flex justify-between">
                                <h4 class="font-medium text-slate-800">{{ $update->date->toFormattedDateString() }}</h4>
                                <span class="text-xs text-slate-500">{{ $update->reviews->count() }} review(s)</span>
                            </div>
                            <p class="mt-2 text-slate-700 whitespace-pre-wrap">{{ $update->wins }}</p>

                            @if ($update->reviews->isNotEmpty())
                                <div class="mt-4 grid md:grid-cols-2 gap-3">
                                    @foreach ($update->reviews as $review)
                                        <div class="rounded-xl border border-slate-200 p-3">
                                            <p class="font-medium text-slate-800">{{ $review->rating }}/10 • {{ str($review->status)->replace('_', ' ') }}</p>
                                            <p class="text-sm text-slate-500">By {{ $review->reviewer->name }}</p>
                                            <p class="mt-1 text-slate-700">{{ $review->comment ?: 'No comment' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="md-card">
                        <div class="md-card-body text-slate-500">No updates submitted yet.</div>
                    </div>
                @endforelse
            </div>

            <div>{{ $updates->links() }}</div>
        </div>
    </div>
</x-app-layout>

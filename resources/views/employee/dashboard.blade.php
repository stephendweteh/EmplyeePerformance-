<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Employee Dashboard</h2>
    </x-slot>

    @php($announcementChunks = $announcements->chunk(3)->values())

    <div class="py-8" x-data="{ selectedImage: null, selectedImageName: '', liveSlideIndex: 0, liveTotalSlides: {{ max($announcementChunks->count(), 1) }} }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="md-card">
                <div class="md-card-body">
                    <h3 class="text-lg font-medium text-slate-800 inline-flex items-center gap-2">
                        <span>Live Updates</span>
                        <span class="relative flex h-2.5 w-2.5" aria-hidden="true">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                    </h3>
                    @if ($announcementChunks->isNotEmpty())
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm text-slate-500">Showing 3 live updates per slide</p>
                            <div class="flex items-center gap-2">
                                <button type="button" class="rounded-md border border-slate-300 px-3 py-1 text-sm text-slate-700 disabled:opacity-40" x-on:click="liveSlideIndex = Math.max(0, liveSlideIndex - 1)" x-bind:disabled="liveSlideIndex === 0">Prev</button>
                                <span class="text-xs text-slate-500" x-text="`Slide ${liveSlideIndex + 1} of ${liveTotalSlides}`"></span>
                                <button type="button" class="rounded-md border border-slate-300 px-3 py-1 text-sm text-slate-700 disabled:opacity-40" x-on:click="liveSlideIndex = Math.min(liveTotalSlides - 1, liveSlideIndex + 1)" x-bind:disabled="liveSlideIndex === liveTotalSlides - 1">Next</button>
                            </div>
                        </div>

                        @foreach ($announcementChunks as $chunkIndex => $announcementChunk)
                            <div x-show="liveSlideIndex === {{ $chunkIndex }}" class="mt-4 grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                                @foreach ($announcementChunk as $announcement)
                                    <div class="rounded-xl border border-slate-200 p-4">
                                        <div class="flex justify-between items-start gap-3">
                                            <div>
                                                <p class="font-medium text-slate-800">{{ $announcement->title }}</p>
                                                <p class="text-xs text-slate-500">By {{ $announcement->author->name }} • {{ $announcement->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if ($announcement->reads->isEmpty())
                                                <form method="POST" action="{{ route('announcements.read', $announcement) }}">
                                                    @csrf
                                                    <button class="text-xs rounded-md bg-slate-900 text-white px-2 py-1">Mark Read</button>
                                                </form>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-slate-700 whitespace-pre-wrap">{{ $announcement->body }}</p>
                                        @php($imageAttachments = $announcement->attachments->filter(fn ($attachment) => $attachment->mime_type && str_starts_with($attachment->mime_type, 'image/')))
                                        @if ($imageAttachments->isNotEmpty())
                                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                                @foreach ($imageAttachments as $attachment)
                                                    <button
                                                        type="button"
                                                        class="relative aspect-[4/3] w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100 hover:border-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 transition"
                                                        x-on:click="selectedImage = @js($attachment->fileUrl()); selectedImageName = @js($attachment->file_name)"
                                                    >
                                                        <img src="{{ $attachment->fileUrl() }}" alt="{{ $attachment->file_name }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if ($announcement->attachments->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @foreach ($announcement->attachments->reject(fn ($attachment) => $attachment->mime_type && str_starts_with($attachment->mime_type, 'image/')) as $attachment)
                                                    <a href="{{ $attachment->fileUrl() }}" target="_blank" class="text-xs px-2 py-1 rounded-full bg-sky-100 text-sky-700 hover:bg-sky-200">
                                                        {{ $attachment->file_name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                        <div class="mt-4">
                            <p class="text-slate-500">No live updates available.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="md-card">
                <div class="md-card-body">
                    <h3 class="text-lg font-medium text-slate-800">Post Daily Win</h3>
                    @if ($errors->any())
                        <div class="mt-3 rounded-lg bg-rose-50 text-rose-700 px-3 py-2 text-sm">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('employee-updates.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4">
                        @csrf
                        <div>
                            <label class="text-sm text-slate-700">Date</label>
                            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Wins</label>
                            <textarea name="wins" rows="3" class="mt-1 w-full rounded-lg border-slate-300" required>{{ old('wins') }}</textarea>
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Business Impact</label>
                            <textarea name="business_impact" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('business_impact') }}</textarea>
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Blockers / Needs Help</label>
                            <textarea name="blockers" rows="2" class="mt-1 w-full rounded-lg border-slate-300">{{ old('blockers') }}</textarea>
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Tags (comma separated)</label>
                            <input type="text" name="tags" value="{{ old('tags') }}" class="mt-1 w-full rounded-lg border-slate-300" placeholder="sales, onboarding">
                        </div>
                        <div>
                            <label class="text-sm text-slate-700">Attachments (optional)</label>
                            <input type="file" name="attachments[]" multiple class="mt-1 w-full rounded-lg border-slate-300" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.webp,.txt,.csv,.xlsx,.xls">
                            <p class="mt-1 text-xs text-slate-500">Up to 5 files, max 10MB each.</p>
                        </div>
                        <div>
                            <button class="inline-flex items-center rounded-lg bg-sky-600 text-white px-4 py-2 font-medium hover:bg-sky-700">Save Update</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div class="md-card">
                    <div class="md-card-body">
                        <h3 class="text-lg font-medium text-slate-800">My History</h3>
                        <div class="mt-4 space-y-4">
                            @forelse ($updates as $update)
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="font-medium text-slate-800">{{ $update->date->toFormattedDateString() }}</p>
                                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full">{{ $update->reviews->count() }} review(s)</span>
                                    </div>
                                    <p class="mt-2 text-slate-700 whitespace-pre-wrap">{{ $update->wins }}</p>
                                    @if ($update->attachments->isNotEmpty())
                                        @php($histImages = $update->attachments->filter(fn ($a) => $a->mime_type && str_starts_with($a->mime_type, 'image/')))
                                        @if ($histImages->isNotEmpty())
                                            <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 max-w-xl">
                                                @foreach ($histImages as $attachment)
                                                    <button
                                                        type="button"
                                                        class="relative aspect-[4/3] w-full overflow-hidden rounded-lg border border-slate-200 bg-slate-100 hover:border-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 transition"
                                                        x-on:click="selectedImage = @js($attachment->fileUrl()); selectedImageName = @js($attachment->file_name)"
                                                    >
                                                        <img src="{{ $attachment->fileUrl() }}" alt="{{ $attachment->file_name }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($update->attachments->reject(fn ($a) => $a->mime_type && str_starts_with($a->mime_type, 'image/')) as $attachment)
                                                <a href="{{ $attachment->fileUrl() }}" target="_blank" rel="noopener noreferrer" class="text-xs px-2 py-1 rounded-full bg-sky-100 text-sky-700 hover:bg-sky-200">
                                                    {{ $attachment->file_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($update->reviews->isNotEmpty())
                                        <div class="mt-3 space-y-2">
                                            @foreach ($update->reviews as $review)
                                                <div class="rounded-lg bg-emerald-50 p-3 text-sm">
                                                    <p class="font-medium text-emerald-800">Rating: {{ $review->rating }}/10 ({{ str($review->status)->replace('_', ' ') }})</p>
                                                    <p class="text-emerald-700">{{ $review->comment ?: 'No comment' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-slate-500">No updates yet.</p>
                            @endforelse
                        </div>
                        <div class="mt-4">{{ $updates->links() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="selectedImage" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" x-on:click.self="selectedImage = null">
            <div class="max-w-5xl w-full">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-white text-sm" x-text="selectedImageName"></p>
                    <button type="button" class="text-white/90 hover:text-white text-sm" x-on:click="selectedImage = null">Close</button>
                </div>
                <img :src="selectedImage" :alt="selectedImageName" class="mx-auto max-h-[min(85vh,900px)] max-w-full rounded-lg bg-black/20 object-contain shadow-lg">
            </div>
        </div>
    </div>
</x-app-layout>

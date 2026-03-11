<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight inline-flex items-center gap-2">
            <span>Live Updates</span>
            <span class="relative flex h-2.5 w-2.5" aria-hidden="true">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
            </span>
        </h2>
    </x-slot>

    @php($announcementChunks = $announcements->getCollection()->chunk(3)->values())

    <div class="py-8" x-data="{ selectedImage: null, selectedImageName: '', slideIndex: 0, totalSlides: {{ max($announcementChunks->count(), 1) }} }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if ($announcementChunks->isNotEmpty())
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Showing 3 live updates per slide</p>
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded-md border border-slate-300 px-3 py-1 text-sm text-slate-700 disabled:opacity-40" x-on:click="slideIndex = Math.max(0, slideIndex - 1)" x-bind:disabled="slideIndex === 0">Prev</button>
                        <span class="text-xs text-slate-500" x-text="`Slide ${slideIndex + 1} of ${totalSlides}`"></span>
                        <button type="button" class="rounded-md border border-slate-300 px-3 py-1 text-sm text-slate-700 disabled:opacity-40" x-on:click="slideIndex = Math.min(totalSlides - 1, slideIndex + 1)" x-bind:disabled="slideIndex === totalSlides - 1">Next</button>
                    </div>
                </div>

                @foreach ($announcementChunks as $chunkIndex => $announcementChunk)
                    <div x-show="slideIndex === {{ $chunkIndex }}" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($announcementChunk as $announcement)
                            <div class="md-card">
                                <div class="md-card-body">
                                    <div class="flex justify-between items-start gap-4">
                                        <div>
                                            <p class="text-lg font-semibold text-slate-800">{{ $announcement->title }}</p>
                                            <p class="text-xs text-slate-500">By {{ $announcement->author->name }} • {{ $announcement->created_at->toDayDateTimeString() }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @can('update', $announcement)
                                                <a href="{{ route('announcements.edit', $announcement) }}" class="rounded-md bg-amber-100 text-amber-700 px-3 py-1 text-sm hover:bg-amber-200">Edit</a>
                                            @endcan
                                            @can('delete', $announcement)
                                                <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Remove this live update?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="rounded-md bg-rose-100 text-rose-700 px-3 py-1 text-sm hover:bg-rose-200">Remove</button>
                                                </form>
                                            @endcan
                                            @if ($announcement->reads->isEmpty())
                                                <form method="POST" action="{{ route('announcements.read', $announcement) }}">
                                                    @csrf
                                                    <button class="rounded-md bg-slate-900 text-white px-3 py-1 text-sm">Mark Read</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="mt-3 text-slate-700 whitespace-pre-wrap">{{ $announcement->body }}</p>
                                    @php($imageAttachments = $announcement->attachments->filter(fn ($attachment) => $attachment->mime_type && str_starts_with($attachment->mime_type, 'image/')))
                                    @if ($imageAttachments->isNotEmpty())
                                        <div class="mt-4 grid grid-cols-2 gap-3">
                                            @foreach ($imageAttachments as $attachment)
                                                <button
                                                    type="button"
                                                    class="rounded-lg overflow-hidden border border-slate-200 hover:border-sky-400 transition"
                                                    x-on:click="selectedImage = @js($attachment->fileUrl()); selectedImageName = @js($attachment->file_name)"
                                                >
                                                    <img src="{{ $attachment->fileUrl() }}" alt="{{ $attachment->file_name }}" class="h-28 w-full object-cover">
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
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @else
                <div class="md-card">
                    <div class="md-card-body text-slate-500">No live updates available yet.</div>
                </div>
            @endif

            <div>{{ $announcements->links() }}</div>
        </div>

        <div x-show="selectedImage" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" x-on:click.self="selectedImage = null">
            <div class="max-w-5xl w-full">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-white text-sm" x-text="selectedImageName"></p>
                    <button type="button" class="text-white/90 hover:text-white text-sm" x-on:click="selectedImage = null">Close</button>
                </div>
                <img :src="selectedImage" :alt="selectedImageName" class="max-h-[80vh] w-full object-contain rounded-lg bg-black/20">
            </div>
        </div>
    </div>
</x-app-layout>

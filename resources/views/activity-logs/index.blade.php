<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Activity Feed</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($logs as $log)
                <div class="md-card">
                    <div class="md-card-body">
                        <div class="flex justify-between gap-4">
                            <div>
                                <p class="font-medium text-slate-800">{{ $log->plainActionText() }}</p>
                                <p class="text-sm text-slate-500">{{ $log->actorLabel() }}</p>
                            </div>
                            <p class="text-xs text-slate-500">{{ $log->created_at->toDayDateTimeString() }}</p>
                        </div>
                        @if (count($log->plainMetaLines()) > 0)
                            <div class="mt-2 rounded-lg bg-slate-50 border border-slate-200 p-2 text-xs text-slate-700 space-y-1">
                                @foreach ($log->plainMetaLines() as $line)
                                    <p>{{ $line }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="md-card">
                    <div class="md-card-body text-slate-500">No activity yet.</div>
                </div>
            @endforelse

            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>

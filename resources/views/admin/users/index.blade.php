<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Users</h2>
            <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-sky-600 text-white px-4 py-2 text-sm hover:bg-sky-700">Create User</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-emerald-100 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-rose-100 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="md-card overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-600 border-b border-slate-200">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Role</th>
                            <th class="px-4 py-3 font-medium">Primary Team</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700">{{ $user->role }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $user->team?->name ?? 'No primary team' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-xs px-2 py-1 rounded-full bg-sky-100 text-sky-700 hover:bg-sky-200">View</a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700 hover:bg-amber-200">Edit</a>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs px-2 py-1 rounded-full bg-rose-100 text-rose-700 hover:bg-rose-200">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                Use View for profile and activity details. Use Edit to update role, team, password, and privileges.
            </p>
        </div>
    </div>
</x-app-layout>

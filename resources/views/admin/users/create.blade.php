<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Create User</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="md-card">
                <div class="md-card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                        @csrf

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm text-slate-700">Name</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                                @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm text-slate-700">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-slate-300" required>
                                @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm text-slate-700">Password</label>
                                <input id="password" name="password" type="password" class="mt-1 w-full rounded-lg border-slate-300" required>
                                @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm text-slate-700">Confirm Password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full rounded-lg border-slate-300" required>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="role" class="block text-sm text-slate-700">Role</label>
                                <select id="role" name="role" class="mt-1 w-full rounded-lg border-slate-300" required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}" @selected(old('role', 'employee') === $role)>{{ $role }}</option>
                                    @endforeach
                                </select>
                                @error('role')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="team_id" class="block text-sm text-slate-700">Primary Team</label>
                                <select id="team_id" name="team_id" class="mt-1 w-full rounded-lg border-slate-300">
                                    <option value="">None</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected((string) old('team_id') === (string) $team->id)>{{ $team->name }}</option>
                                    @endforeach
                                </select>
                                @error('team_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-700">Privileges</label>
                            <div class="mt-2 grid sm:grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach ($privileges as $privilege)
                                    <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-2 py-1 text-sm">
                                        <input type="checkbox" name="permissions[]" value="{{ $privilege }}" class="rounded border-slate-300" @checked(in_array($privilege, old('permissions', []), true))>
                                        {{ $privilege }}
                                    </label>
                                @endforeach
                            </div>
                            @error('permissions')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            @error('permissions.*')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="submit" class="rounded-lg bg-sky-600 text-white px-4 py-2 text-sm hover:bg-sky-700">Create User</button>
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-600 hover:underline">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

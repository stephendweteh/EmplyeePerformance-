<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('team')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => User::roles(),
            'privileges' => User::privileges(),
            'teams' => Team::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::roles())],
            'team_id' => ['nullable', 'exists:teams,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(User::privileges())],
        ]);

        $permissionMap = [];
        foreach (User::privileges() as $privilege) {
            $permissionMap[$privilege] = in_array($privilege, $validated['permissions'] ?? [], true);
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'team_id' => $validated['team_id'] ?? null,
            'permissions' => $permissionMap,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => User::roles(),
            'privileges' => User::privileges(),
            'teams' => Team::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::roles())],
            'team_id' => ['nullable', 'exists:teams,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(User::privileges())],
        ]);

        if ($request->user()->id === $user->id && $validated['role'] !== 'super_admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own super admin access.',
            ]);
        }

        $permissionMap = [];
        foreach (User::privileges() as $privilege) {
            $permissionMap[$privilege] = in_array($privilege, $validated['permissions'] ?? [], true);
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'team_id' => $validated['team_id'] ?? null,
            'permissions' => $permissionMap,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->withErrors([
                'user' => 'At least one super admin account must remain.',
            ]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function updateAccess(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(User::roles())],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(User::privileges())],
        ]);

        if ($request->user()->id === $user->id && $validated['role'] !== 'super_admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own super admin access.',
            ]);
        }

        $permissionMap = [];
        foreach (User::privileges() as $privilege) {
            $permissionMap[$privilege] = in_array($privilege, $validated['permissions'] ?? [], true);
        }

        $user->update([
            'role' => $validated['role'],
            'permissions' => $permissionMap,
        ]);

        return back()->with('success', 'User access updated.');
    }

    public function show(User $user): View
    {
        $user->load([
            'team',
            'teams',
            'updates.reviews.reviewer',
            'reviewsGiven.employeeUpdate.user',
            'activityLogs',
        ]);

        return view('admin.users.show', [
            'user' => $user,
            'privileges' => User::privileges(),
        ]);
    }
}

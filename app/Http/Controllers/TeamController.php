<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('teams.index', [
            'teams' => Team::withCount('members')->orderBy('name')->get(),
            'employees' => User::where('role', 'employee')->with(['team', 'teams'])->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:teams,name'],
            'description' => ['nullable', 'string'],
        ]);

        Team::create($validated);

        return back()->with('success', 'Team created.');
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('teams', 'name')->ignore($team->id)],
            'description' => ['nullable', 'string'],
        ]);

        $team->update($validated);

        return back()->with('success', 'Team updated.');
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'team_id' => ['required', 'exists:teams,id'],
            'set_primary' => ['nullable', 'boolean'],
        ]);

        $user = User::where('role', 'employee')->findOrFail($validated['user_id']);
        $user->teams()->syncWithoutDetaching([$validated['team_id']]);

        if (! array_key_exists('set_primary', $validated) || (bool) $validated['set_primary']) {
            $user->update(['team_id' => $validated['team_id']]);
        }

        ActivityLogger::log(auth()->id(), 'team.member_added', $user, [
            'team_id' => $validated['team_id'],
            'set_primary' => ! array_key_exists('set_primary', $validated) || (bool) $validated['set_primary'],
        ]);

        return back()->with('success', 'Employee assigned to team.');
    }

    public function removeMember(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'team_id' => ['required', 'exists:teams,id'],
        ]);

        $user = User::where('role', 'employee')->with('teams')->findOrFail($validated['user_id']);

        if ($user->teams->count() <= 1) {
            return back()->withErrors([
                'team_id' => 'An employee must belong to at least one team.',
            ]);
        }

        $user->teams()->detach($validated['team_id']);

        if ((int) $user->team_id === (int) $validated['team_id']) {
            $newPrimary = $user->teams()->first();
            $user->update(['team_id' => $newPrimary?->id]);
        }

        ActivityLogger::log(auth()->id(), 'team.member_removed', $user, [
            'team_id' => $validated['team_id'],
        ]);

        return back()->with('success', 'Team membership removed.');
    }

    public function setPrimary(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'team_id' => ['required', 'exists:teams,id'],
        ]);

        $user = User::where('role', 'employee')->findOrFail($validated['user_id']);

        if (! $user->teams()->where('teams.id', $validated['team_id'])->exists()) {
            return back()->withErrors([
                'team_id' => 'Employee must belong to that team before setting it as primary.',
            ]);
        }

        $user->update(['team_id' => $validated['team_id']]);

        ActivityLogger::log(auth()->id(), 'team.primary_set', $user, [
            'team_id' => $validated['team_id'],
        ]);

        return back()->with('success', 'Primary team updated.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    public function show(User $user): View
    {
        abort_unless($user->isEmployee(), 404);

        $user->load(['team', 'updates.reviews.reviewer']);

        return view('employees.show', [
            'employee' => $user,
            'updates' => $user->updates()->with(['reviews.reviewer'])->orderByDesc('date')->paginate(12),
        ]);
    }
}

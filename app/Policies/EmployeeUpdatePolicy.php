<?php

namespace App\Policies;

use App\Models\EmployeeUpdate;
use App\Models\User;

class EmployeeUpdatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EmployeeUpdate $employeeUpdate): bool
    {
        return $user->isEmployer() || $employeeUpdate->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isEmployee();
    }

    public function update(User $user, EmployeeUpdate $employeeUpdate): bool
    {
        if (! $user->isEmployee() || $employeeUpdate->user_id !== $user->id) {
            return false;
        }

        $cutoffHour = (int) config('portal.edit_cutoff_hour', 18);

        if (! $employeeUpdate->date->isToday()) {
            return false;
        }

        return now()->lt(now()->copy()->startOfDay()->addHours($cutoffHour));
    }

    public function delete(User $user, EmployeeUpdate $employeeUpdate): bool
    {
        return false;
    }

    public function restore(User $user, EmployeeUpdate $employeeUpdate): bool
    {
        return false;
    }

    public function forceDelete(User $user, EmployeeUpdate $employeeUpdate): bool
    {
        return false;
    }
}

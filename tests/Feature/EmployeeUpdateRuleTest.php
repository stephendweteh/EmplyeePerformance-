<?php

namespace Tests\Feature;

use App\Models\EmployeeUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeUpdateRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_multiple_updates_per_day(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        EmployeeUpdate::create([
            'user_id' => $employee->id,
            'date' => now()->toDateString(),
            'wins' => 'First update',
            'business_impact' => null,
            'blockers' => null,
            'tags' => null,
        ]);

        $response = $this->actingAs($employee)->from(route('employee.dashboard'))->post(route('employee-updates.store'), [
            'date' => now()->toDateString(),
            'wins' => 'Second update',
            'business_impact' => 'Impact',
            'blockers' => 'None',
            'tags' => 'daily',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, EmployeeUpdate::where('user_id', $employee->id)->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\EmployeeUpdate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerDashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_filter_uses_membership_and_supports_multi_team_users(): void
    {
        $teamA = Team::create(['name' => 'Sales', 'description' => null]);
        $teamB = Team::create(['name' => 'Operations', 'description' => null]);

        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $employeeA = User::factory()->create([
            'name' => 'Employee A',
            'role' => 'employee',
            'team_id' => $teamA->id,
        ]);

        $employeeB = User::factory()->create([
            'name' => 'Employee B',
            'role' => 'employee',
            'team_id' => $teamB->id,
        ]);

        $employeeC = User::factory()->create([
            'name' => 'Employee C',
            'role' => 'employee',
            'team_id' => $teamB->id,
        ]);

        $employeeA->teams()->sync([$teamA->id]);
        $employeeB->teams()->sync([$teamB->id]);
        $employeeC->teams()->sync([$teamB->id, $teamA->id]);

        EmployeeUpdate::create([
            'user_id' => $employeeA->id,
            'date' => now()->toDateString(),
            'wins' => 'A wins',
            'business_impact' => null,
            'blockers' => null,
            'tags' => null,
        ]);

        EmployeeUpdate::create([
            'user_id' => $employeeB->id,
            'date' => now()->toDateString(),
            'wins' => 'B wins',
            'business_impact' => null,
            'blockers' => null,
            'tags' => null,
        ]);

        EmployeeUpdate::create([
            'user_id' => $employeeC->id,
            'date' => now()->toDateString(),
            'wins' => 'C wins',
            'business_impact' => null,
            'blockers' => null,
            'tags' => null,
        ]);

        $response = $this->actingAs($employer)->get(route('employer.dashboard', [
            'date' => now()->toDateString(),
            'team_id' => $teamA->id,
        ]));

        $response->assertOk();
        $response->assertSee('Employee A');
        $response->assertSee('Employee C');
        $response->assertDontSee('Employee B');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_access_employer_dashboard(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)->get(route('employer.dashboard'));

        $response->assertForbidden();
    }

    public function test_employer_cannot_access_employee_dashboard(): void
    {
        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $response = $this->actingAs($employer)->get(route('employee.dashboard'));

        $response->assertForbidden();
    }

    public function test_employee_cannot_open_announcement_create_screen(): void
    {
        $employee = User::factory()->create([
            'role' => 'employee',
        ]);

        $response = $this->actingAs($employee)->get(route('announcements.create'));

        $response->assertForbidden();
    }

    public function test_announcement_target_validation_is_strict(): void
    {
        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $team = Team::create([
            'name' => 'Engineering',
            'description' => 'Product engineering',
        ]);

        $responseForAll = $this->actingAs($employer)->from(route('announcements.create'))->post(route('announcements.store'), [
            'title' => 'Global update',
            'body' => 'This is for everyone.',
            'target_type' => 'all',
            'team_target_id' => $team->id,
        ]);

        $responseForAll->assertSessionHasErrors(['team_target_id']);

        $responseForTeam = $this->actingAs($employer)->from(route('announcements.create'))->post(route('announcements.store'), [
            'title' => 'Team update',
            'body' => 'This is for one team.',
            'target_type' => 'team',
        ]);

        $responseForTeam->assertSessionHasErrors(['team_target_id']);
    }
}

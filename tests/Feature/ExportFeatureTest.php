<?php

namespace Tests\Feature;

use App\Models\EmployeeUpdate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_export_csv(): void
    {
        $team = Team::create(['name' => 'Sales', 'description' => null]);

        $employer = User::factory()->create([
            'role' => 'employer',
        ]);

        $employee = User::factory()->create([
            'name' => 'Export Employee',
            'role' => 'employee',
            'team_id' => $team->id,
        ]);

        $employee->teams()->sync([$team->id]);

        EmployeeUpdate::create([
            'user_id' => $employee->id,
            'date' => now()->toDateString(),
            'wins' => 'Exportable win',
            'business_impact' => 'Export impact',
            'blockers' => null,
            'tags' => null,
        ]);

        $response = $this->actingAs($employer)->get(route('exports.updates.csv', [
            'date' => now()->toDateString(),
            'team_id' => $team->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}

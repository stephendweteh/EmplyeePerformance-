<?php

namespace Tests\Feature;

use App\Models\EmployeeUpdate;
use App\Models\Team;
use App\Models\User;
use App\Notifications\EmployeeUpdateSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmployeeUpdateNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_update_submission_notifies_employer_side_users(): void
    {
        Notification::fake();

        $team = Team::create([
            'name' => 'Engineering',
            'description' => 'Product and engineering',
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
            'team_id' => $team->id,
        ]);

        $employer = User::factory()->create(['role' => 'employer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($employee)->post(route('employee-updates.store'), [
            'date' => now()->toDateString(),
            'wins' => 'Completed API integration.',
            'business_impact' => 'Reduced manual effort.',
            'blockers' => '',
            'tags' => 'api,delivery',
        ]);

        $response->assertSessionHasNoErrors();

        $update = EmployeeUpdate::query()->firstOrFail();

        Notification::assertSentTo(
            [$employer, $admin, $superAdmin],
            EmployeeUpdateSubmittedNotification::class,
            function (EmployeeUpdateSubmittedNotification $notification) use ($update) {
                $payload = $notification->toArray(new User);

                return $payload['employee_update_id'] === $update->id;
            }
        );
    }
}

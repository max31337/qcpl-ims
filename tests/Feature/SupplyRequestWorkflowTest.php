<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SupplyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SupplyRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_submit_supply_request_and_admin_and_officer_can_approve()
    {
        Notification::fake();

        // Seed database
        $this->artisan('db:seed');

        // Get staff, admin, and supply officer users
        $staff = User::where('email', 'staff.project7@qcpl.gov.ph')->first();
        $admin = User::where('email', 'admin@qcpl.gov.ph')->first();
        $officer = User::where('email', 'supply.project7@qcpl.gov.ph')->first();

        $this->assertNotNull($staff, 'Staff user not found');
        $this->assertNotNull($admin, 'Admin user not found');
        $this->assertNotNull($officer, 'Supply officer not found');

        // Staff submits a supply request
        $this->actingAs($staff);
        // Use the correct route and payload for the Livewire supply request form
        $response = $this->post('/supplies/create', [
            'items' => [1], // Assuming supply with ID 1 exists
            'quantities' => [1 => 10],
        ]);
        $response->assertStatus(302);
        $request = SupplyRequest::where('user_id', $staff->id)->latest()->first();
        $this->assertNotNull($request, 'Supply request not created');
        $this->assertEquals('pending', $request->status);

        // Admin approves the request
        $this->actingAs($admin);
        $response = $this->post('/supplies/approve', [
            'request_id' => $request->id,
            'action' => 'approve',
        ]);
        $response->assertStatus(302);
        $request->refresh();
        $this->assertEquals('admin_approved', $request->status);

        // Supply officer approves the request
        $this->actingAs($officer);
        $response = $this->post('/supplies/approve', [
            'request_id' => $request->id,
            'action' => 'approve',
        ]);
        $response->assertStatus(302);
        $request->refresh();
        $this->assertEquals('supply_officer_approved', $request->status);

        // Notifications sent
        Notification::assertSentTo($staff, \App\Notifications\SupplyRequestStatusChanged::class);
    }
}

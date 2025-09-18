<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Reminder;

class ReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReminderService $reminderService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reminderService = new ReminderService();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_returns_reminders_in_order_and_limits_take()
    {
        $user = User::factory()->create();

        Reminder::create([
            'title' => 'A',
            'description' => 'X',
            'remind_at' => 1000,
            'event_at' => 2000,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        Reminder::create([
            'title' => 'B',
            'description' => 'Y',
            'remind_at' => 500,
            'event_at' => 2500,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        Reminder::create([
            'title' => 'C',
            'description' => 'Z',
            'remind_at' => 2000,
            'event_at' => 3000,
            'status' => 'pending',
            'created_by' => $user->id,
        ]);

        $result = $this->reminderService->list(3);

        $this->assertCount(3, $result);

        $remindAts = $result->pluck('remind_at')->toArray();
        $expected = [500, 1000, 2000];
        $this->assertEquals($expected, $remindAts);
    }

    /** @test */
    public function it_can_find_a_reminder_by_id()
    {
        $reminder = Reminder::create([
            'title' => 'Test',
            'description' => 'Desc',
            'remind_at' => 1000,
            'event_at' => 2000,
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        $found = $this->reminderService->find($reminder->id);

        $this->assertNotNull($found);
        $this->assertEquals($reminder->id, $found->id);
    }

    /** @test */
    public function it_can_create_a_reminder()
    {
        $data = [
            'title' => 'New',
            'description' => 'Created',
            'remind_at' => 1234,
            'event_at' => 5678,
            'status' => 'pending'
        ];

        $reminder = $this->reminderService->create($data);

        $this->assertDatabaseHas('reminders', [
            'id' => $reminder->id,
            'title' => 'New',
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function it_can_update_a_reminder()
    {
        $reminder = Reminder::create([
            'title' => 'Old',
            'description' => 'OldDesc',
            'remind_at' => 1000,
            'event_at' => 2000,
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        $updated = $this->reminderService->update($reminder->id, [
            'title' => 'Updated',
            'status' => 'done'
        ]);

        $this->assertEquals('Updated', $updated->title);
        $this->assertEquals('done', $updated->status);
    }

    /** @test */
    public function it_can_delete_a_reminder()
    {
        $reminder = Reminder::create([
            'title' => 'ToDelete',
            'description' => 'X',
            'remind_at' => 1000,
            'event_at' => 2000,
            'status' => 'pending',
            'created_by' => $this->user->id
        ]);

        $deleted = $this->reminderService->delete($reminder->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('reminders', ['id' => $reminder->id]);
    }
}
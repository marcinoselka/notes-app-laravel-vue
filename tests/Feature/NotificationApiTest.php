<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_own_notifications_only(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Notification::factory(3)->for($user)->create();
        Notification::factory(2)->for($otherUser)->create();

        $response = $this->actingAs($user)->getJson('/api/notifications');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_marking_a_notification_as_read_sets_read_at(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user)->create();

        $response = $this->actingAs($user)->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->patchJson("/api/notifications/{$notification->id}/read")
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_only_affects_the_authenticated_users_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Notification::factory(3)->for($user)->create();
        $otherNotification = Notification::factory()->for($otherUser)->create();

        $this->actingAs($user)->patchJson('/api/notifications/read-all')->assertOk();

        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id, 'read_at' => null]);
        $this->assertNull($otherNotification->fresh()->read_at);
    }
}

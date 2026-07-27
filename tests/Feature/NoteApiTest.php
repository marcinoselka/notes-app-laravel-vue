<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_note(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/notes', [
            'title' => 'My first note',
            'content' => 'Some content',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'My first note')
            ->assertJsonPath('data.is_pinned', false);

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'title' => 'My first note',
        ]);
    }

    public function test_creating_a_note_without_a_title_fails_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/notes', [
            'content' => 'No title provided',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    public function test_authenticated_user_can_list_only_their_own_notes(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Note::factory(3)->for($user)->create();
        Note::factory(5)->for($otherUser)->create();

        $response = $this->actingAs($user)->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    public function test_notes_list_is_paginated_with_15_per_page_by_default(): void
    {
        $user = User::factory()->create();
        Note::factory(20)->for($user)->create();

        $response = $this->actingAs($user)->getJson('/api/notes');

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_user_cannot_access_another_users_note(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = Note::factory()->for($otherUser)->create();

        $this->actingAs($user)->getJson("/api/notes/{$note->id}")->assertForbidden();
        $this->actingAs($user)->putJson("/api/notes/{$note->id}", ['title' => 'hacked'])->assertForbidden();
        $this->actingAs($user)->deleteJson("/api/notes/{$note->id}")->assertForbidden();

        $this->assertDatabaseHas('notes', ['id' => $note->id, 'title' => $note->title]);
    }

    public function test_user_can_update_and_delete_their_own_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user)->create(['title' => 'Original']);

        $this->actingAs($user)
            ->putJson("/api/notes/{$note->id}", ['title' => 'Updated', 'is_pinned' => true])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated')
            ->assertJsonPath('data.is_pinned', true);

        $this->actingAs($user)->deleteJson("/api/notes/{$note->id}")->assertNoContent();

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_guest_cannot_access_notes_endpoints(): void
    {
        $this->getJson('/api/notes')->assertUnauthorized();
    }

    public function test_note_service_enforces_the_100_note_limit_per_user(): void
    {
        $user = User::factory()->create();
        Note::factory(100)->for($user)->create();

        $response = $this->actingAs($user)->postJson('/api/notes', [
            'title' => 'One note too many',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('notes', 100);
    }
}

<?php

namespace Tests\Feature;

use App\Mail\ContactReceived;
use App\Models\ContactMessage;
use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\ContactMessageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MatchaApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sanctum only starts a session for requests that look like they come from your SPA
     * (matching Origin/Referer), so the auth tests pretend to be the frontend.
     */
    private function asSpa(): static
    {
        config(['sanctum.stateful' => ['localhost']]);

        return $this->withHeaders(['Referer' => 'http://localhost']);
    }

    private function item(array $overrides = []): MenuItem
    {
        return MenuItem::create($overrides + [
            'name' => 'Chai Matcha',
            'description' => 'Spiced chai and matcha.',
            'price' => 150,
        ]);
    }

    public function test_public_menu_only_lists_available_items(): void
    {
        $this->item(['name' => 'Visible']);
        $this->item(['name' => 'Hidden', 'is_available' => false]);

        $this->getJson('/api/menu')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Visible');
    }

    public function test_contact_form_saves_message_and_emails_the_owner(): void
    {
        Mail::fake();
        config(['matcha.notify_email' => 'owner@example.com']);

        $this->postJson('/api/contact', [
            'email' => 'visitor@example.com',
            'subject' => 'Hello',
            'message' => 'Do you have oat milk?',
        ])->assertCreated();

        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com']);
        Mail::assertSent(ContactReceived::class, fn ($mail) => $mail->hasTo('owner@example.com'));
    }

    public function test_contact_form_validates_input(): void
    {
        $this->postJson('/api/contact', ['email' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'subject', 'message']);
    }

    public function test_register_creates_a_normal_user_and_ignores_is_admin(): void
    {
        $this->asSpa()->postJson('/api/register', [
            'name' => 'Mika',
            'email' => 'mika@example.com',
            'password' => 'a-good-password',
            'password_confirmation' => 'a-good-password',
            'is_admin' => true,
        ])->assertCreated()->assertJsonPath('user.is_admin', false);

        $this->assertFalse(User::where('email', 'mika@example.com')->first()->is_admin);
    }

    public function test_login_rejects_wrong_password_and_accepts_right_one(): void
    {
        User::factory()->create(['email' => 'mika@example.com', 'password' => 'a-good-password']);

        $this->asSpa()->postJson('/api/login', ['email' => 'mika@example.com', 'password' => 'wrong'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->asSpa()->postJson('/api/login', ['email' => 'mika@example.com', 'password' => 'a-good-password'])
            ->assertOk()
            ->assertJsonPath('user.email', 'mika@example.com');
    }

    public function test_me_is_null_for_visitors_and_filled_for_users(): void
    {
        $this->getJson('/api/me')->assertOk()->assertJsonPath('user', null);

        Sanctum::actingAs(User::factory()->create(['name' => 'Mika']));
        $this->getJson('/api/me')->assertOk()->assertJsonPath('user.name', 'Mika');
    }

    public function test_logout_requires_login(): void
    {
        $this->postJson('/api/logout')->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->asSpa()->postJson('/api/logout')->assertNoContent();
    }

    public function test_admin_routes_reject_guests_and_regular_users(): void
    {
        $this->getJson('/api/admin/messages')->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/admin/messages')->assertForbidden();
        $this->postJson('/api/admin/menu', [])->assertForbidden();
    }

    public function test_admin_can_read_and_manage_messages(): void
    {
        $message = ContactMessage::create(['email' => 'a@b.com', 'subject' => 'Hi', 'message' => 'Hello']);
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));

        $this->getJson('/api/admin/messages')->assertOk()->assertJsonCount(1);

        $this->postJson("/api/admin/messages/{$message->id}/read")->assertOk();
        $this->assertNotNull($message->fresh()->read_at);

        $this->deleteJson("/api/admin/messages/{$message->id}")->assertNoContent();
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_admin_can_create_update_and_delete_menu_items(): void
    {
        Sanctum::actingAs(User::factory()->create(['is_admin' => true]));

        $payload = [
            'name' => 'Hojicha Latte',
            'description' => 'Roasted green tea with milk.',
            'price' => 140,
            'image' => '/hojicha.jpg',
            'is_available' => true,
            'sort_order' => 5,
        ];

        $id = $this->postJson('/api/admin/menu', $payload)->assertCreated()->json('id');

        $this->putJson("/api/admin/menu/{$id}", [...$payload, 'price' => 160])
            ->assertOk()
            ->assertJsonPath('price', 160);

        $this->postJson('/api/admin/menu', [...$payload, 'image' => 'javascript:alert(1)'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');

        $this->deleteJson("/api/admin/menu/{$id}")->assertNoContent();
        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_sample_messages_seed_50_rows_and_do_not_duplicate(): void
    {
        $this->seed(ContactMessageSeeder::class);
        $this->seed(ContactMessageSeeder::class);

        $this->assertDatabaseCount('contact_messages', 50);
    }

    public function test_admin_seeder_makes_only_the_configured_email_an_admin(): void
    {
        config(['matcha.admin.email' => 'owner@example.com', 'matcha.admin.password' => 'long-secret-pass']);
        $someoneElse = User::factory()->create();

        $this->seed(AdminUserSeeder::class);

        $this->assertTrue(User::where('email', 'owner@example.com')->first()->is_admin);
        $this->assertFalse($someoneElse->fresh()->is_admin);
    }
}

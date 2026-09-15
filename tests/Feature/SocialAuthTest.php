<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSocialiteUser(string $id, string $email, string $name): SocialiteUser
    {
        $socialiteUser = new SocialiteUser();
        $socialiteUser->id = $id;
        $socialiteUser->email = $email;
        $socialiteUser->name = $name;

        return $socialiteUser;
    }

    public function test_redirect_uses_the_requested_provider(): void
    {
        Socialite::shouldReceive('driver->redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/mock'));

        $this->get('/login/google')->assertRedirect('https://accounts.google.com/o/oauth2/mock');
    }

    public function test_unknown_provider_is_rejected(): void
    {
        $this->get('/login/facebook')->assertNotFound();
        $this->get('/login/facebook/callback')->assertNotFound();
    }

    public function test_first_time_login_creates_an_account(): void
    {
        $socialiteUser = $this->fakeSocialiteUser('abc123', 'creator@example.com', 'Creator One');
        Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

        $response = $this->get('/login/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'creator@example.com',
            'provider_name' => 'google',
            'provider_id' => 'abc123',
        ]);
        $this->assertAuthenticated();
    }

    public function test_existing_email_gets_linked_to_the_provider(): void
    {
        $existing = User::factory()->create(['email' => 'creator@example.com']);

        $socialiteUser = $this->fakeSocialiteUser('xyz789', 'creator@example.com', 'Creator One');
        Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);

        $this->get('/login/discord/callback');

        $existing->refresh();
        $this->assertSame('discord', $existing->provider_name);
        $this->assertSame('xyz789', $existing->provider_id);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_returning_provider_user_logs_straight_in(): void
    {
        $socialiteUser = $this->fakeSocialiteUser('abc123', 'creator@example.com', 'Creator One');
        Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser);
        $this->get('/login/google/callback');

        $this->assertSame(1, User::count());

        $socialiteUser2 = $this->fakeSocialiteUser('abc123', 'creator@example.com', 'Creator One');
        Socialite::shouldReceive('driver->user')->once()->andReturn($socialiteUser2);
        $this->post('/logout');
        $this->get('/login/google/callback');

        $this->assertSame(1, User::count());
        $this->assertAuthenticated();
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_login_with_valid_credentials_succeeds(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * @dataProvider protectedRouteProvider
     */
    #[DataProvider('protectedRouteProvider')]
    public function test_guests_are_redirected_to_login_for_protected_routes(string $method, string $uri): void
    {
        $response = $this->call($method, $uri);

        $response->assertRedirect('/login');
    }

    public static function protectedRouteProvider(): array
    {
        return [
            'dashboard'  => ['GET', '/'],
            'customers'  => ['GET', '/customers'],
            'products'   => ['GET', '/products'],
            'templates'  => ['GET', '/templates'],
            'history'    => ['GET', '/history'],
            'settings'   => ['GET', '/settings/company'],
        ];
    }

    public function test_authenticated_user_can_reach_the_login_page_is_redirected_away(): void
    {
        // The 'guest' middleware on GET /login should bounce an already
        // authenticated user rather than showing the login form again.
        $this->actingAs(User::factory()->create());

        $response = $this->get('/login');

        $response->assertRedirect();
        $response->assertStatus(302);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    public function test_guest_is_sent_to_login_from_protected_pages(): void
    {
        foreach (['/cart', '/orders', '/orders/create', '/profile', '/dashboard', '/posts', '/products', '/users', '/categories'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_guest_cannot_use_protected_actions(): void
    {
        $this->post('/cart/1')->assertRedirect('/login');
        $this->patch('/cart/1')->assertRedirect('/login');
        $this->delete('/cart/1')->assertRedirect('/login');
        $this->post('/orders')->assertRedirect('/login');
        $this->patch('/password')->assertRedirect('/login');
        $this->post('/cafes/1/reviews')->assertRedirect('/login');
        $this->post('/products/1/reviews')->assertRedirect('/login');
        $this->post('/posts')->assertRedirect('/login');
        $this->post('/logout')->assertRedirect('/login');
    }

    public function test_customer_logs_in_and_goes_to_home(): void
    {
        $user = $this->makeUser();

        $this->post('/login', ['email' => $user->email, 'password' => 'password-123'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_shop_owner_logs_in_and_goes_to_dashboard(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);

        $this->post('/login', ['email' => $owner->email, 'password' => 'password-123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'salah-banget'])
            ->assertRedirect('/login')
            ->assertSessionHas('error', 'Email atau password salah.');

        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);

        $this->post('/login', ['email' => 'bukan-email', 'password' => 'x'])->assertSessionHasErrors('email');
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->makeUser();

        for ($i = 1; $i <= 5; $i++) {
            $this->from('/login')
                ->post('/login', ['email' => $user->email, 'password' => 'salah'])
                ->assertRedirect('/login');
        }

        // Percobaan ke-6 ditolak walaupun passwordnya benar.
        $this->post('/login', ['email' => $user->email, 'password' => 'password-123'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs($this->makeUser())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_logged_in_user_cannot_open_login_or_register_pages(): void
    {
        $this->actingAs($this->makeUser());

        $this->get('/login')->assertRedirect();
        $this->get('/register')->assertRedirect();
    }

    public function test_registration_rejects_bad_input(): void
    {
        // Email sengaja dibuat tidak valid supaya tes tidak melakukan pengecekan DNS ke internet.
        $this->post('/register', [])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'bukan-email',
            'password' => 'rahasia-123', 'password_confirmation' => 'rahasia-123',
        ])->assertSessionHasErrors('email');

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'bukan-email',
            'password' => 'pendek', 'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->post('/register', [
            'name' => 'Budi', 'email' => 'bukan-email',
            'password' => 'rahasia-123', 'password_confirmation' => 'beda-sekali',
        ])->assertSessionHasErrors('password');

        $this->post('/register', [
            'name' => str_repeat('a', 101), 'email' => 'bukan-email',
            'password' => 'rahasia-123', 'password_confirmation' => 'rahasia-123',
        ])->assertSessionHasErrors('name');

        $this->assertSame(0, User::count());
    }

    public function test_role_and_member_cannot_be_mass_assigned(): void
    {
        // role & member tidak fillable: nilai kiriman dari form tidak boleh ikut tersimpan.
        $user = new User();
        $user->fill(['name' => 'Budi', 'email' => 'budi@example.test', 'password' => 'x', 'role' => 'admin', 'member' => 'diamond']);

        $this->assertNull($user->role);
        $this->assertNull($user->member);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->patch('/password', [
                'current_password' => 'password-123',
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('password-baru-456', $user->fresh()->password));
    }

    public function test_password_change_is_rejected_with_wrong_current_password_or_bad_new_password(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->patch('/password', [
                'current_password' => 'salah',
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)
            ->patch('/password', [
                'current_password' => 'password-123',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password-123', $user->fresh()->password));
    }

    public function test_profile_page_opens_for_logged_in_user(): void
    {
        $this->actingAs($this->makeUser())->get('/profile')->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    public function test_public_pages_open_when_database_is_empty(): void
    {
        foreach (['/', '/login', '/register'] as $url) {
            $status = $this->get($url)->getStatusCode();
            $this->assertSame(200, $status, "Halaman {$url} mengembalikan status {$status}, seharusnya 200.");
        }
    }

    public function test_home_lists_cafes_and_filters_by_search_and_category(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $coffee = $this->makeCategory('Kopi');
        $tea = $this->makeCategory('Teh');
        $this->makeShop($owner, $coffee, ['title' => 'Kopi Alpha']);
        $this->makeShop($owner, $tea, ['title' => 'Teh Beta']);

        $this->get('/')->assertOk()->assertSee('Kopi Alpha')->assertSee('Teh Beta');

        $this->get('/?search=Alpha')->assertOk()->assertSee('Kopi Alpha')->assertDontSee('Teh Beta');

        $this->get('/?category='.$tea->id)->assertOk()->assertSee('Teh Beta')->assertDontSee('Kopi Alpha');
    }

    public function test_home_accepts_known_sorts_and_ignores_unknown_ones(): void
    {
        $this->makeShop($this->makeUser(User::ROLE_OWNER), null, ['title' => 'Kopi Alpha']);

        foreach (['rating_desc', 'rating_asc', 'ngawur; DROP TABLE posts'] as $sort) {
            $this->get('/?sort='.urlencode($sort))->assertOk()->assertSee('Kopi Alpha');
        }
    }

    public function test_search_with_special_characters_does_not_crash(): void
    {
        $this->get('/?search='.urlencode('%_\\'))->assertOk();
    }

    public function test_cafe_detail_shows_products_and_reviews(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $shop = $this->makeShop($owner, null, ['title' => 'Kopi Alpha']);
        $this->makeProduct($shop, 25000, ['title' => 'Latte Spesial']);
        $this->makeReview($this->makeUser(), $shop, 5, 'Tempatnya nyaman sekali');

        $this->get("/cafes/{$shop->id}")
            ->assertOk()
            ->assertSee('Kopi Alpha')
            ->assertSee('Latte Spesial')
            ->assertSee('Tempatnya nyaman sekali');
    }

    public function test_product_detail_page_opens(): void
    {
        $shop = $this->makeShop($this->makeUser(User::ROLE_OWNER));
        $product = $this->makeProduct($shop, 25000, ['title' => 'Latte Spesial']);

        $this->get("/products/{$product->id}")->assertOk()->assertSee('Latte Spesial');
    }

    public function test_unknown_ids_return_404(): void
    {
        foreach (['/cafes/999', '/cafes/abc', '/products/999'] as $url) {
            $this->get($url)->assertNotFound();
        }
    }

    public function test_non_numeric_product_id_is_never_served(): void
    {
        // Rute publik hanya menerima angka, tetapi bentuk URL-nya sama dengan rute dashboard
        // (PATCH/DELETE /products/{id}), jadi Laravel membalas 405 untuk GET, bukan 404.
        $status = $this->get('/products/abc')->getStatusCode();

        $this->assertContains($status, [404, 405], "Status {$status} tidak terduga untuk /products/abc.");
    }

    public function test_pages_send_security_headers(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_user_generated_text_is_escaped(): void
    {
        $shop = $this->makeShop($this->makeUser(User::ROLE_OWNER), null, ['title' => '<b>Tebal</b>']);
        $this->makeReview($this->makeUser(), $shop, 4, "<script>alert('xss')</script>");

        $this->get('/')
            ->assertDontSee('<b>Tebal</b>', false)
            ->assertSee('&lt;b&gt;Tebal&lt;/b&gt;', false);

        $this->get("/cafes/{$shop->id}")
            ->assertDontSee("<script>alert('xss')</script>", false)
            ->assertSee('&lt;script&gt;', false);
    }
}

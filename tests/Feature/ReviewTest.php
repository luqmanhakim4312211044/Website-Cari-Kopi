<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    private function shop()
    {
        return $this->makeShop($this->makeUser(User::ROLE_OWNER));
    }

    public function test_user_can_review_a_cafe(): void
    {
        $user = $this->makeUser();
        $shop = $this->shop();

        $this->actingAs($user)
            ->post("/cafes/{$shop->id}/reviews", ['rating' => 5, 'comment' => 'Enak sekali'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'reviewable_type' => 'post',
            'reviewable_id' => $shop->id,
            'rating' => 5,
            'comment' => 'Enak sekali',
        ]);
    }

    public function test_user_can_review_a_product_and_the_comment_is_optional(): void
    {
        $user = $this->makeUser();
        $product = $this->makeProduct($this->shop());

        $this->actingAs($user)
            ->post("/products/{$product->id}/reviews", ['rating' => 4])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'reviewable_type' => 'product',
            'reviewable_id' => $product->id,
            'rating' => 4,
        ]);
    }

    public function test_user_can_only_review_the_same_item_once(): void
    {
        $user = $this->makeUser();
        $shop = $this->shop();

        $this->actingAs($user)->post("/cafes/{$shop->id}/reviews", ['rating' => 5]);
        $this->actingAs($user)->post("/cafes/{$shop->id}/reviews", ['rating' => 1])->assertSessionHas('error');

        $this->assertSame(1, Review::count());
        $this->assertSame(5, Review::first()->rating);
    }

    public function test_review_input_is_validated(): void
    {
        $user = $this->makeUser();
        $shop = $this->shop();

        foreach ([0, 6, 'abc', '', null, 3.5] as $rating) {
            $this->actingAs($user)->post("/cafes/{$shop->id}/reviews", ['rating' => $rating])->assertSessionHasErrors('rating');
        }

        $this->actingAs($user)
            ->post("/cafes/{$shop->id}/reviews", ['rating' => 5, 'comment' => str_repeat('a', 501)])
            ->assertSessionHasErrors('comment');

        $this->assertSame(0, Review::count());
    }

    public function test_reviewing_an_unknown_item_returns_404(): void
    {
        $this->actingAs($this->makeUser())->post('/cafes/999/reviews', ['rating' => 5])->assertNotFound();
        $this->actingAs($this->makeUser())->post('/products/999/reviews', ['rating' => 5])->assertNotFound();
    }

    public function test_only_the_author_can_edit_a_review(): void
    {
        $author = $this->makeUser();
        $review = $this->makeReview($author, $this->shop(), 5, 'Asli');

        $this->actingAs($this->makeUser())->patch("/reviews/{$review->id}", ['rating' => 1])->assertForbidden();
        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->patch("/reviews/{$review->id}", ['rating' => 1])->assertForbidden();
        $this->assertSame(5, $review->fresh()->rating);

        $this->actingAs($author)->patch("/reviews/{$review->id}", ['rating' => 3, 'comment' => 'Diubah'])->assertSessionHas('success');
        $this->assertSame(3, $review->fresh()->rating);
        $this->assertSame('Diubah', $review->fresh()->comment);
    }

    public function test_author_and_admin_can_delete_a_review_but_others_cannot(): void
    {
        $author = $this->makeUser();
        $shop = $this->shop();

        $review = $this->makeReview($author, $shop);
        $this->actingAs($this->makeUser())->delete("/reviews/{$review->id}")->assertForbidden();
        $this->assertSame(1, Review::count());

        $this->actingAs($author)->delete("/reviews/{$review->id}")->assertSessionHas('success');
        $this->assertSame(0, Review::count());

        $review = $this->makeReview($author, $shop);
        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->delete("/reviews/{$review->id}")->assertSessionHas('success');
        $this->assertSame(0, Review::count());
    }

    public function test_review_posting_is_rate_limited(): void
    {
        $user = $this->makeUser();
        $shop = $this->shop();

        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($user)->post("/cafes/{$shop->id}/reviews", ['rating' => 5]);
        }

        $this->actingAs($user)->post("/cafes/{$shop->id}/reviews", ['rating' => 5])->assertStatus(429);
    }
}

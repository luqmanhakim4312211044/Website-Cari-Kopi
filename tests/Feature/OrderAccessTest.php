<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    public function test_my_orders_lists_only_my_own_orders(): void
    {
        $me = $this->makeUser();
        $other = $this->makeUser();
        $seller = $this->makeUser(User::ROLE_OWNER);
        $mine = $this->makeOrder($me, $seller);
        $this->makeOrder($other, $seller);

        $this->actingAs($me)
            ->get('/orders')
            ->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->getCollection()->pluck('id')->all() === [$mine->id]);
    }

    public function test_my_orders_can_be_filtered_by_status_and_ignores_unknown_status(): void
    {
        $me = $this->makeUser();
        $seller = $this->makeUser(User::ROLE_OWNER);
        $this->makeOrder($me, $seller, Order::STATUS_PENDING);
        $paid = $this->makeOrder($me, $seller, Order::STATUS_PAID);

        $this->actingAs($me)->get('/orders?status=paid')
            ->assertViewHas('orders', fn ($orders) => $orders->getCollection()->pluck('id')->all() === [$paid->id]);

        $this->actingAs($me)->get('/orders?status=ngawur')
            ->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 2);
    }

    public function test_order_detail_is_visible_to_buyer_seller_and_admin_only(): void
    {
        $buyer = $this->makeUser();
        $seller = $this->makeUser(User::ROLE_OWNER);
        $order = $this->makeOrder($buyer, $seller);

        $this->actingAs($buyer)->get("/orders/{$order->id}")->assertOk();
        $this->actingAs($seller)->get("/orders/{$order->id}")->assertOk();
        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get("/orders/{$order->id}")->assertOk();

        $this->actingAs($this->makeUser())->get("/orders/{$order->id}")->assertForbidden();
        $this->actingAs($this->makeUser(User::ROLE_OWNER))->get("/orders/{$order->id}")->assertForbidden();
    }

    public function test_customers_cannot_open_the_dashboard(): void
    {
        $customer = $this->makeUser();

        foreach (['/dashboard', '/dashboard/orders', '/dashboard/report', '/posts', '/products'] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }
    }

    public function test_shop_owner_and_admin_can_open_the_dashboard(): void
    {
        foreach ([User::ROLE_OWNER, User::ROLE_ADMIN] as $role) {
            $user = $this->makeUser($role);

            foreach (['/dashboard', '/dashboard/orders'] as $url) {
                $status = $this->actingAs($user)->get($url)->getStatusCode();
                $this->assertSame(200, $status, "{$role} seharusnya bisa membuka {$url}, tapi statusnya {$status}.");
            }
        }
    }

    public function test_incoming_orders_are_scoped_to_the_seller(): void
    {
        $buyer = $this->makeUser();
        $ownerA = $this->makeUser(User::ROLE_OWNER);
        $ownerB = $this->makeUser(User::ROLE_OWNER);
        $forA = $this->makeOrder($buyer, $ownerA);
        $forB = $this->makeOrder($buyer, $ownerB);

        $this->actingAs($ownerA)->get('/dashboard/orders')
            ->assertViewHas('orders', fn ($orders) => $orders->getCollection()->pluck('id')->all() === [$forA->id]);

        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get('/dashboard/orders')
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 2);
    }

    public function test_dashboard_statistics_only_count_the_owners_own_data(): void
    {
        $buyer = $this->makeUser();
        $owner = $this->makeUser(User::ROLE_OWNER);
        $otherOwner = $this->makeUser(User::ROLE_OWNER);

        $shop = $this->makeShop($owner);
        $this->makeProduct($shop);
        $this->makeShop($otherOwner);

        $this->makeOrder($buyer, $owner, Order::STATUS_PENDING, 10000);
        $this->makeOrder($buyer, $owner, Order::STATUS_PAID, 30000);
        $this->makeOrder($buyer, $otherOwner, Order::STATUS_PAID, 99000);

        $this->actingAs($owner)->get('/dashboard')
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['cafes'] === 1
                && $stats['products'] === 1
                && $stats['pending'] === 1
                && $stats['revenue'] === 30000);
    }

    public function test_seller_and_admin_can_update_the_order_status(): void
    {
        $buyer = $this->makeUser();
        $seller = $this->makeUser(User::ROLE_OWNER);
        $order = $this->makeOrder($buyer, $seller);

        $this->actingAs($seller)->patch("/dashboard/orders/{$order->id}", ['status' => 'paid'])->assertSessionHas('success');
        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);

        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->patch("/dashboard/orders/{$order->id}", ['status' => 'pending']);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_other_owners_and_customers_cannot_update_the_order_status(): void
    {
        $buyer = $this->makeUser();
        $seller = $this->makeUser(User::ROLE_OWNER);
        $order = $this->makeOrder($buyer, $seller);

        $this->actingAs($this->makeUser(User::ROLE_OWNER))->patch("/dashboard/orders/{$order->id}", ['status' => 'paid'])->assertForbidden();
        $this->actingAs($buyer)->patch("/dashboard/orders/{$order->id}", ['status' => 'paid'])->assertForbidden();

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_order_status_must_be_a_known_value(): void
    {
        $seller = $this->makeUser(User::ROLE_OWNER);
        $order = $this->makeOrder($this->makeUser(), $seller);

        $this->actingAs($seller)->patch("/dashboard/orders/{$order->id}", ['status' => 'lunas'])->assertSessionHasErrors('status');
        $this->actingAs($seller)->patch("/dashboard/orders/{$order->id}", [])->assertSessionHasErrors('status');

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_incoming_order_search_does_not_crash_on_special_characters(): void
    {
        $seller = $this->makeUser(User::ROLE_OWNER);

        $this->actingAs($seller)->get('/dashboard/orders?search='.urlencode("%_\\' OR 1=1 --"))->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesShopData;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use CreatesShopData, RefreshDatabase;

    public function test_customers_and_guests_cannot_open_the_report(): void
    {
        $this->get('/dashboard/report')->assertRedirect('/login');
        $this->get('/dashboard/report/pdf')->assertRedirect('/login');

        $customer = $this->makeUser();
        $this->actingAs($customer)->get('/dashboard/report')->assertForbidden();
        $this->actingAs($customer)->get('/dashboard/report/pdf')->assertForbidden();
    }

    public function test_owner_report_only_counts_own_sales(): void
    {
        $buyer = $this->makeUser();
        $owner = $this->makeUser(User::ROLE_OWNER);
        $other = $this->makeUser(User::ROLE_OWNER);

        $this->makeOrder($buyer, $owner, Order::STATUS_PAID, 30000);
        $this->makeOrder($buyer, $owner, Order::STATUS_PENDING, 10000);
        $this->makeOrder($buyer, $other, Order::STATUS_PAID, 99000);

        $this->actingAs($owner)->get('/dashboard/report')
            ->assertOk()
            ->assertViewHas('report', fn ($report) => $report['totals']['revenue'] === 30000
                && $report['totals']['pending_amount'] === 10000
                && $report['totals']['orders'] === 2);
    }

    public function test_admin_report_covers_the_whole_site(): void
    {
        $buyer = $this->makeUser();
        $this->makeOrder($buyer, $this->makeUser(User::ROLE_OWNER), Order::STATUS_PAID, 30000);
        $this->makeOrder($buyer, $this->makeUser(User::ROLE_OWNER), Order::STATUS_PAID, 20000);

        $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get('/dashboard/report')
            ->assertOk()
            ->assertViewHas('report', fn ($report) => $report['totals']['revenue'] === 50000);
    }

    public function test_report_accepts_a_date_range_and_the_all_time_option(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);

        $this->actingAs($owner)->get('/dashboard/report?from=2026-01-01&to=2026-12-31')->assertOk();
        $this->actingAs($owner)->get('/dashboard/report?all=1')->assertOk();
    }

    public function test_report_rejects_an_invalid_date_range(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);

        $this->actingAs($owner)->get('/dashboard/report?from=2026-10-10&to=2026-10-01')->assertSessionHasErrors('to');
        $this->actingAs($owner)->get('/dashboard/report?from=bukan-tanggal')->assertSessionHasErrors('from');
    }

    public function test_pdf_report_is_a_real_pdf_file(): void
    {
        $owner = $this->makeUser(User::ROLE_OWNER);
        $this->makeOrder($this->makeUser(), $owner, Order::STATUS_PAID, 30000);

        $response = $this->actingAs($owner)->get('/dashboard/report/pdf');

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('laporan-penghasilan', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_report_works_for_the_admin_even_without_orders(): void
    {
        $response = $this->actingAs($this->makeUser(User::ROLE_ADMIN))->get('/dashboard/report/pdf');

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}

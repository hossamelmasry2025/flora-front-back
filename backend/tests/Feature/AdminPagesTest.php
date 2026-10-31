<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_page_renders(): void
    {
        $this->seed(StoreSeeder::class);
        $this->actingAs(User::factory()->create());

        $order = Order::create([
            'payment_method' => 'transfer', 'payment_proof' => 'payment-proofs/x.jpg', 'payment_status' => 'under_review',
            'sender_name' => 'أحمد', 'sender_phone' => '01012345678',
            'recipient_name' => 'سارة', 'recipient_phone' => '01112345678', 'address' => 'مدينة نصر',
            'card_message' => "كل سنة وانتي طيبة\nبحبك", 'subtotal' => 850, 'delivery_fee' => 60, 'total' => 910,
            'delivery_date' => today(),
        ]);
        $order->items()->create(['product_id' => 1, 'product_name' => 'بوكيه', 'price' => 850, 'quantity' => 1, 'total' => 850]);

        $urls = [
            '/admin',
            '/admin/orders', '/admin/orders?tab=today', "/admin/orders/{$order->id}", "/admin/orders/{$order->id}/edit",
            '/admin/products', '/admin/products/create', '/admin/products/'.Product::first()->id.'/edit',
            '/admin/categories', '/admin/categories/create', '/admin/categories/'.Category::first()->id.'/edit',
            '/admin/pages', '/admin/pages/create', '/admin/pages/1/edit',
            '/admin/delivery-zones', '/admin/users', '/admin/appearance',
            '/admin/slides', '/admin/slides/create', '/admin/slides/'.\App\Models\Slide::first()->id.'/edit',
            '/admin/banners', '/admin/banners/create', '/admin/banners?tab=category', '/admin/banners/'.\App\Models\Banner::first()->id.'/edit',
            '/admin/home-sections', '/admin/home-sections/create',
            ...\App\Models\HomeSection::pluck('id')->map(fn ($id) => "/admin/home-sections/{$id}/edit")->all(),
            '/admin/settings',
            "/admin-tools/orders/{$order->id}/print",
        ];

        foreach ($urls as $url) {
            $this->assertSame(200, $this->get($url)->status(), "Failed: {$url}");
        }
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/orders')->assertRedirect('/admin/login');
        $this->get('/admin-tools/orders/1/print')->assertRedirect('/admin/login');
    }
}

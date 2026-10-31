<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StoreSeeder::class);
    }

    public function test_public_endpoints(): void
    {
        $this->getJson('/api/settings')->assertOk()->assertJsonPath('data.site_name', 'فلورا');
        $this->getJson('/api/home')->assertOk()->assertJsonStructure(['data' => [['type', 'data']]]);
        $this->getJson('/api/categories')->assertOk()->assertJsonCount(6, 'data');
        $this->getJson('/api/sitemap')->assertOk();

        $product = Product::first();
        $this->getJson('/api/products/'.rawurlencode($product->slug))->assertOk()->assertJsonPath('data.id', $product->id);
        $this->getJson('/api/products/not-here')->assertNotFound();
    }

    public function test_product_listing_search_filter_and_pagination(): void
    {
        $this->getJson('/api/products?per_page=5&page=2')->assertOk()
            ->assertJsonPath('meta.current_page', 2)->assertJsonPath('meta.total', 12)->assertJsonCount(5, 'data');

        $this->getJson('/api/products?q='.urlencode('توليب'))->assertOk()->assertJsonPath('meta.total', 1);

        $prices = collect($this->getJson('/api/products?sort=price_asc&per_page=48')->json('data'))->pluck('final_price');
        $this->assertEquals($prices->sort()->values()->all(), $prices->all());

        $love = $this->getJson('/api/products?category='.urlencode('ورد-رومانسي'))->json('meta.total');
        $this->assertSame(4, $love);
    }

    public function test_cash_on_delivery_order_uses_server_prices(): void
    {
        $product = Product::where('sale_price', '>', 0)->first();
        $zone = DeliveryZone::first();

        $res = $this->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'price' => 1]],
            'sender_name' => 'أحمد', 'sender_phone' => '01012345678',
            'recipient_name' => 'سارة', 'recipient_phone' => '01112345678',
            'delivery_zone_id' => $zone->id, 'address' => 'شارع عباس العقاد',
            'delivery_date' => today()->addDay()->toDateString(),
            'card_message' => 'كل سنة وانتي طيبة', 'payment_method' => 'cod',
        ])->assertCreated();

        $expected = $product->sale_price * 2 + $zone->fee;
        $this->assertEquals($expected, $res->json('data.total'));
        $res->assertJsonPath('data.status', 'pending');

        $order = Order::first();
        $this->assertSame('كل سنة وانتي طيبة', $order->card_message);
        $this->assertSame('unpaid', $order->payment_status);

        $this->getJson("/api/orders/{$order->number}?phone=010-1234-5678")->assertOk();
        $this->getJson("/api/orders/{$order->number}?phone=01099999999")->assertNotFound();
    }

    public function test_transfer_order_requires_screenshot_and_stores_it_privately(): void
    {
        Storage::fake('local');
        $base = [
            'items' => [['product_id' => Product::first()->id, 'quantity' => 1]],
            'sender_name' => 'أحمد', 'sender_phone' => '01012345678',
            'recipient_name' => 'سارة', 'recipient_phone' => '01112345678',
            'delivery_zone_id' => DeliveryZone::first()->id, 'address' => 'التجمع',
            'payment_method' => 'transfer',
        ];

        $this->postJson('/api/orders', $base)->assertUnprocessable()->assertJsonValidationErrors('payment_proof');

        $this->post('/api/orders', $base + ['payment_proof' => UploadedFile::fake()->image('proof.jpg')], ['Accept' => 'application/json'])
            ->assertCreated();

        $order = Order::first();
        $this->assertSame('under_review', $order->payment_status);
        Storage::disk('local')->assertExists($order->payment_proof);
    }

    public function test_hidden_or_out_of_stock_products_cannot_be_ordered(): void
    {
        $product = Product::first();
        $product->update(['in_stock' => false]);

        $this->postJson('/api/orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
            'sender_name' => 'أحمد', 'sender_phone' => '01012345678',
            'recipient_name' => 'سارة', 'recipient_phone' => '01112345678',
            'delivery_zone_id' => DeliveryZone::first()->id, 'address' => 'التجمع', 'payment_method' => 'cod',
        ])->assertUnprocessable()->assertJsonValidationErrors('items');
    }
}

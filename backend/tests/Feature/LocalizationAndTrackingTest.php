<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StoreSeeder::class);
    }

    public function test_english_content_and_slugs(): void
    {
        $this->getJson('/api/settings?lang=en')->assertOk()->assertJsonPath('data.site_name', 'Flora')->assertJsonPath('data.currency', 'EGP');

        $product = $this->getJson('/api/products/pink-roses-bouquet-24-stems?lang=en')->assertOk();
        $product->assertJsonPath('data.name', 'Pink Roses Bouquet — 24 Stems')
            ->assertJsonPath('data.slugs.en', 'pink-roses-bouquet-24-stems');

        // الرابط الإنجليزي شغال في النسخة العربي والعكس
        $ar = $product->json('data.slugs.ar');
        $this->getJson('/api/products/'.rawurlencode($ar).'?lang=en')->assertOk()->assertJsonPath('data.slug', 'pink-roses-bouquet-24-stems');
        $this->getJson('/api/products/pink-roses-bouquet-24-stems')->assertOk()->assertJsonPath('data.name', 'بوكيه روز بينك 24 وردة');

        $this->getJson('/api/pages/about-us?lang=en')->assertOk()->assertJsonPath('data.title', 'About us');
        $this->getJson('/api/products?category=birthday-flowers&lang=en')->assertOk()->assertJsonPath('meta.total', 6);
    }

    public function test_order_keeps_language_notifies_admins_and_tracks_history(): void
    {
        $admin = User::factory()->create();

        $number = $this->postJson('/api/orders?lang=en', [
            'items' => [['product_id' => Product::first()->id, 'quantity' => 1]],
            'sender_name' => 'Ahmed', 'sender_phone' => '01012345678',
            'recipient_name' => 'Sara', 'recipient_phone' => '01112345678',
            'delivery_zone_id' => DeliveryZone::first()->id, 'address' => 'Nasr City', 'payment_method' => 'cod',
        ])->assertCreated()->assertJsonPath('data.status_label', 'Order received')->json('data.number');

        $order = Order::where('number', $number)->first();
        $this->assertSame('en', $order->locale);
        $this->assertCount(1, $admin->notifications);

        $order->update(['status' => 'confirmed']);
        $order->update(['status' => 'out_for_delivery']);

        $this->getJson("/api/orders/{$number}?phone=01012345678&lang=en")->assertOk()
            ->assertJsonPath('data.status_label', 'Out for delivery')
            ->assertJsonCount(3, 'data.history')
            ->assertJsonPath('data.history.2.status', 'out_for_delivery');

        $this->getJson("/api/orders/{$number}?phone=01012345678")->assertOk()->assertJsonPath('data.status_label', 'خرج للتوصيل');
    }

    public function test_validation_messages_follow_language(): void
    {
        $this->postJson('/api/orders', ['items' => []])->assertUnprocessable()
            ->assertJsonPath('errors.sender_name.0', 'حقل اسمك مطلوب.');

        $this->postJson('/api/orders?lang=en', ['items' => []])->assertUnprocessable()
            ->assertJsonPath('errors.sender_name.0', 'The sender name field is required.');
    }
}

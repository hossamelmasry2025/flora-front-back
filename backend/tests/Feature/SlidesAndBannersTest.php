<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Slide;
use Database\Seeders\StoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlidesAndBannersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StoreSeeder::class);
    }

    private function homeSection(string $type): ?array
    {
        return collect($this->getJson('/api/home')->assertOk()->json('data'))->firstWhere('type', $type);
    }

    public function test_slider_uses_visible_slides_in_order_with_settings(): void
    {
        Slide::create(['image' => 'slides/new.jpg', 'title' => 'أول', 'sort_order' => -1, 'text_position' => 'center', 'overlay' => 20]);
        Slide::create(['image' => 'slides/hidden.jpg', 'title' => 'مخفي', 'is_active' => false]);
        Slide::create(['image' => 'slides/later.jpg', 'title' => 'بعدين', 'starts_at' => now()->addDay()]);
        Setting::put('slider_interval', 4);

        $slider = $this->homeSection('slider');
        $this->assertSame('أول', $slider['data']['items'][0]['title']);
        $this->assertSame('center', $slider['data']['items'][0]['text_position']);
        $this->assertSame(4, $slider['data']['settings']['interval']);
        $this->assertNotContains('مخفي', collect($slider['data']['items'])->pluck('title'));
        $this->assertNotContains('بعدين', collect($slider['data']['items'])->pluck('title'));
    }

    public function test_new_home_banner_appears_without_extra_steps(): void
    {
        Banner::create(['image' => 'banners/x.jpg', 'title' => 'عرض جديد', 'placement' => 'home', 'size' => 'half', 'sort_order' => 99]);

        $titles = collect($this->homeSection('banners')['data']['items'])->pluck('title');
        $this->assertContains('عرض جديد', $titles);
    }

    public function test_category_banners_show_only_in_their_category(): void
    {
        [$birthday, $love] = Category::orderBy('sort_order')->take(2)->get();
        Banner::create(['image' => 'banners/b.jpg', 'title' => 'خاص بعيد الميلاد', 'placement' => 'category', 'category_id' => $birthday->id]);
        Banner::create(['image' => 'banners/all.jpg', 'title' => 'لكل التصنيفات', 'placement' => 'category']);

        $birthdayBanners = collect($this->getJson('/api/categories/'.rawurlencode($birthday->slug))->json('data.sections_top.0.data.items'))->pluck('title');
        $loveBanners = collect($this->getJson('/api/categories/'.rawurlencode($love->slug))->json('data.sections_top.0.data.items'))->pluck('title');

        $this->assertEqualsCanonicalizing(['خاص بعيد الميلاد', 'لكل التصنيفات'], $birthdayBanners->all());
        $this->assertSame(['لكل التصنيفات'], $loveBanners->all());
    }

    public function test_other_placements(): void
    {
        Banner::create(['image' => 'banners/c.jpg', 'title' => 'في السلة', 'placement' => 'cart']);
        Banner::create(['image' => 'banners/p.jpg', 'title' => 'تحت المنتج', 'placement' => 'product_page']);

        $this->getJson('/api/sections/cart_page')->assertOk()->assertJsonPath('data.0.data.items.0.title', 'في السلة');
        $this->getJson('/api/products/pink-roses-bouquet-24-stems')->assertOk()->assertJsonPath('sections.0.data.items.0.title', 'تحت المنتج');
        $this->getJson('/api/sections/nope')->assertNotFound();
    }
}

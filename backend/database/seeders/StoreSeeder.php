<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * داتا تجريبية للبداية (عربي + إنجليزي) — عدّلها أو امسحها من الداش بورد.
 */
class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->copyDemoImages();
        $this->settings();
        $this->zones();
        $this->pages();
        $categories = $this->categories();
        $this->products($categories);
        $this->home($categories);
    }

    /** صور تجريبية من Unsplash (مجانية الاستخدام) — بدّلها بصور منتجاتك الحقيقية. */
    private function copyDemoImages(): void
    {
        foreach (File::allFiles(database_path('seeders/demo')) as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, $file->getContents());
            }
        }
    }

    private function image(string $path): ?string
    {
        return Storage::disk('public')->exists($path) ? $path : null;
    }

    private function settings(): void
    {
        foreach ([
            'site_name' => 'فلورا',
            'site_name_en' => 'Flora',
            'tagline' => 'ورد طبيعي يوصل لحبايبك في نفس اليوم',
            'tagline_en' => 'Fresh flowers delivered to your loved ones today',
            'announcement' => '🚚 توصيل في نفس اليوم داخل القاهرة والجيزة للطلبات قبل الساعة 6 مساءً',
            'announcement_en' => '🚚 Same-day delivery across Cairo & Giza for orders placed before 6 PM',
            'footer_about' => 'فلورا متجر ورد أونلاين بنجهز بوكيهات الورد الطبيعي بإيدينا ونوصلها لحد باب البيت، مع كارت مكتوب برسالتك.',
            'footer_about_en' => 'Flora is an online flower shop. We hand-arrange fresh bouquets and deliver them to the door, with a card carrying your message.',
            'currency' => 'ج.م',
            'currency_en' => 'EGP',
            'phone' => '01000000000',
            'whatsapp' => '201000000000',
            'email' => 'hello@flora.example',
            'city' => 'القاهرة',
            'city_en' => 'Cairo',
            'address' => 'القاهرة، مصر',
            'address_en' => 'Cairo, Egypt',
            'working_hours' => 'يومياً من 10 صباحاً حتى 12 منتصف الليل',
            'working_hours_en' => 'Daily, 10 AM – 12 AM',
            'social' => [
                ['platform' => 'facebook', 'url' => 'https://facebook.com/'],
                ['platform' => 'instagram', 'url' => 'https://instagram.com/'],
            ],
            'meta_title' => 'فلورا | محل ورد أونلاين — توصيل بوكيهات ورد طبيعي في القاهرة',
            'meta_title_en' => 'Flora | Online Flower Shop — Same-Day Flower Delivery in Cairo',
            'meta_description' => 'اطلب بوكيه ورد طبيعي أونلاين لأعياد الميلاد والمناسبات مع توصيل في نفس اليوم داخل القاهرة والجيزة وكارت برسالتك. ادفع كاش عند الاستلام أو بالتحويل.',
            'meta_description_en' => 'Order fresh flower bouquets online for birthdays and every occasion, with same-day delivery in Cairo & Giza and a free card with your message.',
            'cod_enabled' => true,
            'transfer_enabled' => true,
            'transfer_accounts' => [
                ['name' => 'InstaPay', 'name_en' => 'InstaPay', 'number' => 'flora@instapay', 'holder' => 'Flora'],
                ['name' => 'فودافون كاش', 'name_en' => 'Vodafone Cash', 'number' => '01000000000', 'holder' => null],
            ],
            'transfer_instructions' => 'حوّل إجمالي الطلب على أي حساب من دول، وارفع سكرين شوت التحويل هنا، وهنأكد طلبك خلال دقايق.',
            'transfer_instructions_en' => 'Transfer the order total to any of these accounts, upload a screenshot here, and we will confirm your order within minutes.',
            'time_slots' => [
                ['label' => 'من 10 صباحاً إلى 2 ظهراً', 'label_en' => '10 AM – 2 PM'],
                ['label' => 'من 2 ظهراً إلى 6 مساءً', 'label_en' => '2 PM – 6 PM'],
                ['label' => 'من 6 مساءً إلى 10 مساءً', 'label_en' => '6 PM – 10 PM'],
            ],
            'min_order' => 0,
            'card_max_length' => 300,
            'same_day_cutoff' => '18:00',
        ] as $key => $value) {
            Setting::put($key, $value);
        }
    }

    private function zones(): void
    {
        $zones = [
            ['مدينة نصر', 'Nasr City', 60], ['مصر الجديدة', 'Heliopolis', 60], ['التجمع الخامس', 'Fifth Settlement', 80],
            ['المعادي', 'Maadi', 70], ['الزمالك', 'Zamalek', 70], ['وسط البلد', 'Downtown', 60],
            ['المهندسين', 'Mohandessin', 70], ['الدقي', 'Dokki', 70], ['الشيخ زايد', 'Sheikh Zayed', 100],
            ['6 أكتوبر', '6th of October', 100], ['الشروق', 'El Shorouk', 90], ['مدينتي', 'Madinaty', 100],
        ];

        foreach ($zones as $i => [$name, $en, $fee]) {
            DeliveryZone::updateOrCreate(['name' => $name], ['name_en' => $en, 'fee' => $fee, 'sort_order' => $i]);
        }
    }

    private function pages(): void
    {
        $content = fn (string $file) => File::get(database_path("seeders/content/{$file}"));

        $pages = [
            [
                'title' => 'من نحن', 'title_en' => 'About us', 'slug_en' => 'about-us',
                'subtitle' => 'حكاية بدأت بوردة… ولسه بتكبر كل يوم', 'subtitle_en' => 'A story that started with one flower — and keeps growing',
                'image' => $this->image('home/banner-1.jpg'),
                'content' => $content('about-ar.html'), 'content_en' => $content('about-en.html'),
                'meta_title' => 'من نحن — حكاية فلورا', 'meta_title_en' => 'About Flora — Our Story',
                'meta_description' => 'تعرّف على فلورا: محل ورد أونلاين في القاهرة بيجهز بوكيهات ورد طبيعي طازة بإيد فنانين ويوصلها في نفس اليوم مع كارت برسالتك.',
                'meta_description_en' => 'Meet Flora: an online florist in Cairo hand-arranging fresh bouquets and delivering them the same day with a card carrying your message.',
            ],
            [
                'title' => 'الشحن والتوصيل', 'title_en' => 'Shipping & delivery', 'slug_en' => 'shipping-delivery',
                'content' => '<p>بنوصل داخل القاهرة والجيزة. الطلبات قبل الساعة 6 مساءً بتوصل في نفس اليوم. رسوم التوصيل بتختلف حسب المنطقة وبتظهر لك قبل تأكيد الطلب.</p>',
                'content_en' => '<p>We deliver across Cairo and Giza. Orders placed before 6 PM are delivered the same day. Delivery fees vary by area and are shown before you confirm your order.</p>',
            ],
            [
                'title' => 'سياسة الاستبدال والاسترجاع', 'title_en' => 'Returns & refunds', 'slug_en' => 'returns-refunds',
                'content' => '<p>لأن الورد منتج طبيعي سريع التلف، لا يمكن استرجاعه بعد الاستلام. لو فيه أي مشكلة في الطلب تواصل معانا خلال ساعتين من الاستلام مع صورة وهنحلها فوراً.</p>',
                'content_en' => '<p>Because flowers are perishable, they cannot be returned after delivery. If there is any issue with your order, contact us within two hours of delivery with a photo and we will fix it right away.</p>',
            ],
            [
                'title' => 'سياسة الخصوصية', 'title_en' => 'Privacy policy', 'slug_en' => 'privacy-policy',
                'content' => '<p>بياناتك وبيانات المستلم بتستخدم بس لتوصيل الطلب والتواصل بخصوصه، ومش بنشاركها مع أي طرف تالت.</p>',
                'content_en' => '<p>Your details and the recipient\'s details are used only to deliver your order and contact you about it. We never share them with third parties.</p>',
            ],
            [
                'title' => 'الشروط والأحكام', 'title_en' => 'Terms & conditions', 'slug_en' => 'terms',
                'content' => '<p>باستخدامك للموقع أنت موافق على الشروط دي. الصور للتوضيح، وممكن يختلف لون أو نوع بعض الورود حسب المتاح في الموسم مع الحفاظ على نفس القيمة والشكل العام.</p>',
                'content_en' => '<p>By using this website you agree to these terms. Photos are for illustration; some flower colours or varieties may vary by season while keeping the same value and overall look.</p>',
            ],
        ];

        foreach ($pages as $i => $page) {
            Page::updateOrCreate(['title' => $page['title']], $page + ['sort_order' => $i]);
        }
    }

    /** @return array<string, Category> */
    private function categories(): array
    {
        $data = [
            'birthday' => ['ورد عيد ميلاد', 'Birthday flowers', 'بوكيهات ورد مبهجة تفرّح اللي بتحبه في يوم ميلاده.', 'Cheerful bouquets to brighten their special day.', 'products/p-graduation.jpg'],
            'love' => ['ورد رومانسي', 'Romantic flowers', 'ورد أحمر وبوكيهات رومانسية لعيد الحب والمناسبات الخاصة.', 'Red roses and romantic bouquets for love and special moments.', 'categories/love.jpg'],
            'graduation' => ['ورد تخرج', 'Graduation flowers', 'هدايا ورد للتخرج والنجاح.', 'Flower gifts to celebrate graduation and success.', 'categories/graduation.jpg'],
            'newborn' => ['ورد مواليد', 'New baby flowers', 'ورد بألوان هادية لاستقبال المولود الجديد.', 'Soft-coloured flowers to welcome a new baby.', 'products/p-baby-pink.jpg'],
            'boxes' => ['بوكس ورد', 'Flower boxes', 'ورد في بوكسات أنيقة تعيش أطول.', 'Elegant flower boxes that last longer.', 'products/p-rainbow.jpg'],
            'gifts' => ['هدايا وشوكولاتة', 'Gifts & chocolates', 'ورد مع شوكولاتة وهدايا مميزة.', 'Flowers paired with chocolates and special gifts.', 'products/p-heart.jpg'],
        ];

        $categories = [];
        $i = 0;
        foreach ($data as $key => [$name, $nameEn, $description, $descriptionEn, $image]) {
            $categories[$key] = Category::updateOrCreate(['name' => $name], [
                'name_en' => $nameEn,
                'description' => $description,
                'description_en' => $descriptionEn,
                'image' => $this->image($image),
                'content' => "<h2>{$name} أونلاين مع توصيل في نفس اليوم</h2><p>اختار من تشكيلة {$name} المجهزة بورد طبيعي طازة، واكتب رسالتك على الكارت وإحنا نوصلها لحد الباب في القاهرة والجيزة.</p>",
                'content_en' => "<h2>{$nameEn} with same-day delivery</h2><p>Choose from our {$nameEn} made with fresh natural blooms, write your message on the card, and we'll deliver it to the door across Cairo and Giza.</p>",
                'sort_order' => $i++,
            ]);
        }

        return $categories;
    }

    /** @param array<string, Category> $c */
    private function products(array $c): void
    {
        $items = [
            ['بوكيه روز بينك 24 وردة', 'Pink Roses Bouquet — 24 Stems', 950, 850, ['birthday', 'love'], true, '24 وردة بينك ملفوفة بورق كوري.', '24 pink roses wrapped in Korean paper.', 'pink-roses', ['single-rose', 'baby-pink']],
            ['بوكيه الشمس المشرقة', 'Sunshine Bouquet', 750, null, ['birthday', 'graduation'], true, 'عباد شمس مع ورد أصفر وأبيض.', 'Sunflowers with yellow and white blooms.', 'sunflowers', ['sunflower-2', 'yellow']],
            ['بوكيه ريد روز كلاسيك 50 وردة', 'Classic Red Roses — 50 Stems', 1800, null, ['love'], true, '50 وردة حمرا للمناسبات الكبيرة.', '50 red roses for the big moments.', 'red-roses', ['single-rose']],
            ['بوكس ورد على شكل قلب', 'Heart-Shaped Flower Box', 1450, 1290, ['love', 'boxes'], true, 'تنسيقة ورد ملونة على شكل قلب.', 'A colourful heart-shaped flower arrangement.', 'heart', []],
            ['بوكيه توليب مشكل', 'Mixed Tulips Bouquet', 1200, null, ['birthday'], false, 'توليب مستورد بألوان مختلفة.', 'Imported tulips in mixed colours.', 'tulips', ['tulip-pink']],
            ['بوكيه بيبي بلو', 'Baby Blue Bouquet', 650, null, ['newborn'], false, 'ورد بألوان هادية لاستقبال المولود.', 'Soft blooms to welcome a baby boy.', 'baby-blue', []],
            ['بوكيه بيبي بينك', 'Baby Pink Bouquet', 650, null, ['newborn'], false, 'ورد بينك وأبيض لاستقبال المولودة.', 'Pink and white blooms to welcome a baby girl.', 'baby-pink', []],
            ['بوكيه التخرج المبهج', 'Graduation Joy Bouquet', 890, null, ['graduation', 'gifts'], true, 'ورد برتقالي وأحمر مبهج للنجاح والتخرج.', 'Bright orange and red blooms to celebrate success.', 'graduation', []],
            ['بوكس ورد قوس قزح', 'Rainbow Roses Box', 1350, null, ['gifts', 'boxes', 'birthday'], true, 'ورد ملون بكل ألوان قوس قزح.', 'Roses in every colour of the rainbow.', 'rainbow', []],
            ['بوكيه جوري أبيض', 'White Garden Bouquet', 800, 720, ['love', 'newborn'], false, 'ورد أبيض مع لمسات بينك هادية.', 'White blooms with soft touches of pink.', 'white', []],
            ['بوكيه داليا فاخر', 'Luxury Dahlia Bouquet', 1100, null, ['birthday'], false, 'داليا بلون الخوخ مع خضرة طبيعية.', 'Peach dahlias with natural greenery.', 'dahlia', []],
            ['بوكس ورد مشكل صغير', 'Petite Mixed Flower Box', 550, null, ['boxes', 'birthday'], false, 'بوكس صغير شيك لكل المناسبات.', 'A small, elegant box for every occasion.', 'mixed-box', []],
        ];

        $img = fn (string $name) => $this->image("products/p-{$name}.jpg");

        foreach ($items as $i => [$name, $nameEn, $price, $sale, $cats, $featured, $short, $shortEn, $image, $gallery]) {
            $product = Product::updateOrCreate(['name' => $name], [
                'name_en' => $nameEn,
                'image' => $img($image),
                'gallery' => collect($gallery)->map($img)->filter()->values()->all(),
                'short_description' => $short,
                'short_description_en' => $shortEn,
                'description' => "<p>{$short} بنجهز البوكيه بورد طبيعي طازة يوم التوصيل، وبيوصل معاه كارت برسالتك.</p><ul><li>ورد طبيعي 100%</li><li>تغليف فاخر</li><li>كارت مجاني برسالتك</li></ul>",
                'description_en' => "<p>{$shortEn} We arrange your bouquet with fresh natural flowers on the day of delivery, along with a card carrying your message.</p><ul><li>100% natural flowers</li><li>Premium wrapping</li><li>Free message card</li></ul>",
                'price' => $price,
                'sale_price' => $sale,
                'is_featured' => $featured,
                'sku' => 'FL-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'details' => [
                    ['label' => 'نوع الورد', 'value' => 'طبيعي طازة', 'label_en' => 'Flowers', 'value_en' => 'Fresh & natural'],
                    ['label' => 'التغليف', 'value' => 'ورق كوري فاخر', 'label_en' => 'Wrapping', 'value_en' => 'Premium Korean paper'],
                ],
                'sort_order' => $i,
            ]);

            $product->categories()->syncWithoutDetaching(collect($cats)->map(fn ($k) => $c[$k]->id));
        }
    }

    /** @param array<string, Category> $c */
    private function home(array $c): void
    {
        if (\App\Models\HomeSection::exists()) {
            return;
        }

        \App\Support\HomeSectionsImporter::import([
            ['type' => 'hero', 'data' => ['is_active' => true, 'slides' => [
                [
                    'image' => $this->image('home/hero-1.jpg'), 'mobile_image' => $this->image('home/hero-1-m.jpg'),
                    'title' => 'ورد طبيعي يوصل لحبايبك النهارده', 'title_en' => 'Fresh flowers, delivered to your loved ones today',
                    'subtitle' => 'بوكيهات مجهزة بإيدينا مع كارت برسالتك وتوصيل في نفس اليوم', 'subtitle_en' => 'Hand-arranged bouquets with your personal card and same-day delivery',
                    'button_text' => 'تسوق الآن', 'button_text_en' => 'Shop now', 'link' => '/products',
                ],
                [
                    'image' => $this->image('home/hero-2.jpg'), 'mobile_image' => $this->image('home/hero-2-m.jpg'),
                    'title' => 'عيد ميلاد سعيد بأحلى ورد', 'title_en' => 'Make their birthday bloom',
                    'subtitle' => 'تشكيلة مبهجة لكل الأعمار', 'subtitle_en' => 'Cheerful bouquets for every age',
                    'button_text' => 'ورد عيد ميلاد', 'button_text_en' => 'Birthday flowers', 'link' => '/categories/'.$c['birthday']->slug,
                ],
            ]]],
            ['type' => 'features', 'data' => ['is_active' => true, 'items' => [
                ['icon' => 'clock', 'title' => 'توصيل في نفس اليوم', 'text' => 'للطلبات قبل 6 مساءً', 'title_en' => 'Same-day delivery', 'text_en' => 'For orders before 6 PM'],
                ['icon' => 'flower', 'title' => 'ورد طبيعي طازة', 'text' => 'بنختاره كل يوم الصبح', 'title_en' => 'Fresh natural flowers', 'text_en' => 'Hand-picked every morning'],
                ['icon' => 'card', 'title' => 'كارت مجاني', 'text' => 'اكتب رسالتك وإحنا نكتبها', 'title_en' => 'Free message card', 'text_en' => 'Your words, beautifully printed'],
                ['icon' => 'wallet', 'title' => 'ادفع براحتك', 'text' => 'كاش عند الاستلام أو تحويل', 'title_en' => 'Pay your way', 'text_en' => 'Cash on delivery or transfer'],
            ]]],
            ['type' => 'categories', 'data' => ['is_active' => true, 'title' => 'تسوق حسب المناسبة', 'title_en' => 'Shop by occasion', 'subtitle' => 'اختار المناسبة وإحنا علينا الباقي', 'subtitle_en' => 'Pick the occasion — we will take care of the rest', 'style' => 'grid']],
            ['type' => 'products', 'data' => ['is_active' => true, 'title' => 'الأكثر طلباً', 'title_en' => 'Best sellers', 'subtitle' => 'البوكيهات اللي عملاءنا بيحبوها', 'subtitle_en' => 'The bouquets our customers love most', 'source' => 'featured', 'style' => 'carousel', 'limit' => 8, 'show_more_link' => true]],
            ['type' => 'banner', 'data' => ['is_active' => true, 'title' => 'اكتب رسالتك وإحنا نوصلها', 'title_en' => 'Write your message, we will deliver it', 'subtitle' => 'كارت مجاني مع كل بوكيه بخط إيد شيك', 'subtitle_en' => 'A free, elegant card with every bouquet', 'image' => $this->image('home/banner-1.jpg'), 'mobile_image' => $this->image('home/banner-1-m.jpg'), 'button_text' => 'اختار البوكيه', 'button_text_en' => 'Choose a bouquet', 'link' => '/products']],
            ['type' => 'products', 'data' => ['is_active' => true, 'title' => 'ورد عيد ميلاد', 'title_en' => 'Birthday flowers', 'source' => 'category', 'category_id' => $c['birthday']->id, 'style' => 'grid', 'limit' => 4, 'show_more_link' => true]],
            ['type' => 'testimonials', 'data' => ['is_active' => true, 'title' => 'عملاءنا بيقولوا إيه', 'title_en' => 'What our customers say', 'items' => [
                ['name' => 'سارة م.', 'name_en' => 'Sara M.', 'rating' => 5, 'text' => 'البوكيه وصل في ميعاده وأحلى من الصورة كمان، ومامتي فرحت جداً.', 'text_en' => 'The bouquet arrived right on time and looked even better than the photo. My mum loved it!'],
                ['name' => 'أحمد ع.', 'name_en' => 'Ahmed A.', 'rating' => 5, 'text' => 'تعامل محترم جداً والكارت اتكتب بخط حلو. أكيد هتعامل تاني.', 'text_en' => 'Very professional service and the card looked beautiful. I will definitely order again.'],
                ['name' => 'منى ك.', 'name_en' => 'Mona K.', 'rating' => 5, 'text' => 'طلبت الساعة 3 ووصل قبل 7، الورد طازة جداً.', 'text_en' => 'Ordered at 3 PM and it arrived before 7. The flowers were so fresh.'],
            ]]],
            ['type' => 'faq', 'data' => ['is_active' => true, 'title' => 'أسئلة شائعة', 'title_en' => 'Frequently asked questions', 'items' => [
                ['question' => 'هل التوصيل في نفس اليوم؟', 'answer' => 'أيوه، الطلبات اللي بتتأكد قبل الساعة 6 مساءً بتوصل في نفس اليوم داخل القاهرة والجيزة.', 'question_en' => 'Do you deliver the same day?', 'answer_en' => 'Yes — orders confirmed before 6 PM are delivered the same day across Cairo and Giza.'],
                ['question' => 'إزاي أكتب رسالة على الكارت؟', 'answer' => 'في صفحة إتمام الطلب فيه خانة للكارت، اكتب فيها رسالتك وإحنا هنطبعها ونحطها مع البوكيه.', 'question_en' => 'How do I add a card message?', 'answer_en' => 'At checkout there is a card field — write your message and we will print it and add it to the bouquet.'],
                ['question' => 'إيه طرق الدفع المتاحة؟', 'answer' => 'تقدر تدفع كاش عند الاستلام، أو تحوّل على إنستاباي / فودافون كاش وترفع سكرين شوت التحويل.', 'question_en' => 'What payment methods do you accept?', 'answer_en' => 'Cash on delivery, or a transfer via InstaPay / Vodafone Cash with a screenshot of the transfer.'],
                ['question' => 'أقدر أتابع طلبي إزاي؟', 'answer' => 'من صفحة "تتبع طلبك" اكتب رقم الطلب ورقم موبايلك وهتشوف حالة طلبك لحظة بلحظة.', 'question_en' => 'How can I track my order?', 'answer_en' => 'Open "Track order", enter your order number and phone, and follow your order step by step.'],
                ['question' => 'ممكن المستلم ما يعرفش مين اللي باعت؟', 'answer' => 'أكيد، فيه اختيار "إخفاء اسم المرسل" في صفحة الطلب.', 'question_en' => 'Can I send flowers anonymously?', 'answer_en' => 'Of course — just tick "hide my name" at checkout.'],
            ]]],
            ['type' => 'text', 'data' => ['is_active' => true, 'title' => 'محل ورد أونلاين في القاهرة', 'title_en' => 'Your online florist in Cairo',
                'content' => '<p>فلورا هو محل ورد أونلاين بيوفرلك بوكيهات ورد طبيعي لكل المناسبات: <strong>ورد عيد ميلاد</strong>، <strong>ورد رومانسي</strong>، ورد تخرج ومواليد، وبوكسات ورد مع شوكولاتة. اطلب من موبايلك وإحنا نوصل الورد لحد الباب في نفس اليوم في مدينة نصر، مصر الجديدة، التجمع، المعادي، الشيخ زايد وأكتوبر.</p>',
                'content_en' => '<p>Flora is an online flower shop offering fresh bouquets for every occasion: <strong>birthday flowers</strong>, <strong>romantic roses</strong>, graduation and new-baby flowers, and flower boxes with chocolates. Order from your phone and we deliver to the door the same day in Nasr City, Heliopolis, New Cairo, Maadi, Sheikh Zayed and 6th of October.</p>',
            ]],
        ]);
    }
}

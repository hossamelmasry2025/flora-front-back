<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * السلايدر والبانرات وأقسام الرئيسية بقوا جداول مستقلة (كل عنصر ليه صفحة تعديل لوحده)
 * بدل ما كانوا كلهم جوه صفحة واحدة. الداتا القديمة بتتنقل تلقائياً.
 */
return new class extends Migration
{
    public function up(): void
    {
        $visual = function (Blueprint $table) {
            $table->string('image');
            $table->string('mobile_image')->nullable();
            $table->string('title')->nullable();
            $table->string('title_en')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_text_en')->nullable();
            $table->string('link')->nullable();
            $table->string('text_position')->default('start'); // start | center | end
            $table->string('text_color')->default('light'); // light | dark
            $table->unsignedTinyInteger('overlay')->default(45); // غمقان الصورة 0-80
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
        };

        Schema::create('slides', function (Blueprint $table) use ($visual) {
            $table->id();
            $visual($table);
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) use ($visual) {
            $table->id();
            $visual($table);
            $table->string('placement')->default('home'); // home | products_page | category | product_page | cart | all_pages_top
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('size')->default('full'); // full | half | third
            $table->string('height')->default('md'); // sm | md | lg
            $table->timestamps();
            $table->index(['placement', 'is_active', 'sort_order']);
        });

        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title')->nullable();
            $table->string('title_en')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('subtitle_en')->nullable();
            $table->json('settings')->nullable();
            $table->string('bg')->default('none');
            $table->string('spacing')->default('md');
            $table->string('align')->default('start');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $this->moveOldHomeSections();
    }

    /** نقل سكاشن الرئيسية القديمة (اللي كانت في الإعدادات) للجداول الجديدة */
    private function moveOldHomeSections(): void
    {
        $row = DB::table('settings')->where('key', 'home_sections')->first();
        $sections = $row ? (json_decode($row->value, true) ?: []) : [];
        $now = now();
        $visual = fn (array $b, int $i) => [
            'image' => $b['image'] ?? '',
            'mobile_image' => $b['mobile_image'] ?? null,
            'title' => $b['title'] ?? null, 'title_en' => $b['title_en'] ?? null,
            'subtitle' => $b['subtitle'] ?? null, 'subtitle_en' => $b['subtitle_en'] ?? null,
            'button_text' => $b['button_text'] ?? null, 'button_text_en' => $b['button_text_en'] ?? null,
            'link' => $b['link'] ?? null,
            'is_active' => true, 'sort_order' => $i, 'created_at' => $now, 'updated_at' => $now,
        ];

        foreach (array_values($sections) as $order => $s) {
            $data = $s['data'] ?? [];
            $type = $s['type'] ?? null;
            $settings = collect($data)->except(['title', 'title_en', 'subtitle', 'subtitle_en', 'is_active', 'bg', 'spacing', 'align', 'slides'])->all();

            if ($type === 'hero') {
                foreach (array_values($data['slides'] ?? []) as $i => $slide) {
                    if (! empty($slide['image'])) {
                        DB::table('slides')->insert($visual($slide, $i));
                    }
                }
                $type = 'slider';
                $settings = [];
            } elseif (in_array($type, ['banner', 'banners_grid'], true)) {
                $items = $type === 'banner' ? [$data] : ($data['items'] ?? []);
                $ids = [];
                foreach (array_values($items) as $i => $b) {
                    if (empty($b['image'])) {
                        continue;
                    }
                    $ids[] = DB::table('banners')->insertGetId($visual($b, $i) + [
                        'placement' => 'home',
                        'size' => $type === 'banner' ? 'full' : (count($items) >= 3 ? 'third' : 'half'),
                        'height' => $data['height'] ?? 'md',
                    ]);
                }
                if (! $ids) {
                    continue;
                }
                $type = 'banners';
                $settings = ['banner_ids' => $ids];
                // عنوان البانر الواحد بقى جوه البانر نفسه
                if ($s['type'] === 'banner') {
                    $data['title'] = $data['title_en'] = $data['subtitle'] = $data['subtitle_en'] = null;
                }
            }

            if (! $type) {
                continue;
            }

            DB::table('home_sections')->insert([
                'type' => $type,
                'title' => $data['title'] ?? null, 'title_en' => $data['title_en'] ?? null,
                'subtitle' => $data['subtitle'] ?? null, 'subtitle_en' => $data['subtitle_en'] ?? null,
                'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
                'bg' => $data['bg'] ?? 'none', 'spacing' => $data['spacing'] ?? 'md', 'align' => $data['align'] ?? 'start',
                'is_active' => (bool) ($data['is_active'] ?? true),
                'sort_order' => $order,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('slides');
    }
};

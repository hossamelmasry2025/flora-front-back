<?php

namespace App\Support;

use App\Models\Banner;
use App\Models\HomeSection;
use App\Models\Slide;
use Illuminate\Support\Arr;

/**
 * بيحوّل وصف بسيط للصفحة الرئيسية لسلايدر + بانرات + أقسام (بيستخدم في الداتا التجريبية).
 */
class HomeSectionsImporter
{
    private const VISUAL = ['image', 'mobile_image', 'title', 'title_en', 'subtitle', 'subtitle_en', 'button_text', 'button_text_en', 'link'];

    public static function import(array $sections): void
    {
        foreach (array_values($sections) as $order => $s) {
            $data = $s['data'] ?? [];
            $type = $s['type'];
            $settings = Arr::except($data, ['title', 'title_en', 'subtitle', 'subtitle_en', 'is_active', 'bg', 'spacing', 'align', 'slides']);
            $titles = Arr::only($data, ['title', 'title_en', 'subtitle', 'subtitle_en']);

            if ($type === 'hero') {
                foreach (array_values($data['slides'] ?? []) as $i => $slide) {
                    Slide::create(Arr::only($slide, self::VISUAL) + ['sort_order' => $i, 'overlay' => 45]);
                }
                $type = 'slider';
                $settings = [];
            } elseif ($type === 'banner') {
                Banner::create(Arr::only($data, self::VISUAL) + ['placement' => 'home', 'size' => 'full', 'height' => 'md', 'overlay' => 45]);
                $type = 'banners';
                $settings = [];
                $titles = [];
            }

            HomeSection::create($titles + [
                'type' => $type,
                'settings' => $settings,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $order,
            ]);
        }
    }
}

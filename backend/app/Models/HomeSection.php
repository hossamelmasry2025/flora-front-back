<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\RevalidatesFrontend;
use Illuminate\Database\Eloquent\Model;

/** قسم في الصفحة الرئيسية (بالترتيب) */
class HomeSection extends Model
{
    use HasTranslations, RevalidatesFrontend;

    public const TYPES = [
        'slider' => 'السلايدر الرئيسي',
        'banners' => 'بانرات',
        'categories' => 'التصنيفات',
        'products' => 'منتجات',
        'features' => 'مميزات المتجر',
        'cta' => 'دعوة للشراء',
        'text' => 'نص / محتوى',
        'video' => 'فيديو',
        'testimonials' => 'آراء العملاء',
        'faq' => 'أسئلة شائعة',
    ];

    public const ICONS = [
        'slider' => 'heroicon-o-photo',
        'banners' => 'heroicon-o-rectangle-group',
        'categories' => 'heroicon-o-squares-2x2',
        'products' => 'heroicon-o-gift',
        'features' => 'heroicon-o-sparkles',
        'cta' => 'heroicon-o-megaphone',
        'text' => 'heroicon-o-document-text',
        'video' => 'heroicon-o-play-circle',
        'testimonials' => 'heroicon-o-chat-bubble-left-ellipsis',
        'faq' => 'heroicon-o-question-mark-circle',
    ];

    protected $fillable = [
        'type', 'title', 'title_en', 'subtitle', 'subtitle_en', 'settings',
        'bg', 'spacing', 'align', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }
}

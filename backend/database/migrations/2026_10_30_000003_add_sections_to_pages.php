<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // سكاشن وبانرات خاصة بكل تصنيف (فوق المنتجات / تحت المنتجات)
        Schema::table('categories', function (Blueprint $table) {
            $table->json('sections_top')->nullable()->after('content_en');
            $table->json('sections_bottom')->nullable()->after('sections_top');
        });

        // سكاشن تحت محتوى الصفحة (من نحن ...)
        Schema::table('pages', function (Blueprint $table) {
            $table->json('sections')->nullable()->after('content_en');
        });
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn(['sections_top', 'sections_bottom']));
        Schema::table('pages', fn (Blueprint $t) => $t->dropColumn('sections'));
    }
};

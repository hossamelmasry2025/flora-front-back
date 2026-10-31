<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('slug_en')->nullable()->unique()->after('slug');
            $table->text('description_en')->nullable()->after('description');
            $table->longText('content_en')->nullable()->after('content');
            $table->string('meta_title_en')->nullable()->after('meta_title');
            $table->text('meta_description_en')->nullable()->after('meta_description');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
            $table->string('slug_en')->nullable()->unique()->after('slug');
            $table->text('short_description_en')->nullable()->after('short_description');
            $table->longText('description_en')->nullable()->after('description');
            $table->string('meta_title_en')->nullable()->after('meta_title');
            $table->text('meta_description_en')->nullable()->after('meta_description');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title');
            $table->string('slug_en')->nullable()->unique()->after('slug');
            $table->string('subtitle')->nullable()->after('title_en');
            $table->string('subtitle_en')->nullable()->after('subtitle');
            $table->string('image')->nullable()->after('subtitle_en');
            $table->longText('content_en')->nullable()->after('content');
            $table->string('meta_title_en')->nullable()->after('meta_title');
            $table->text('meta_description_en')->nullable()->after('meta_description');
        });

        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('locale', 5)->default('ar')->after('number');
        });

        // سجل بكل تغيير في حالة الطلب (للتتبع)
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('locale'));
        Schema::table('delivery_zones', fn (Blueprint $t) => $t->dropColumn('name_en'));
        Schema::table('pages', fn (Blueprint $t) => $t->dropColumn(['title_en', 'slug_en', 'subtitle', 'subtitle_en', 'image', 'content_en', 'meta_title_en', 'meta_description_en']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['name_en', 'slug_en', 'short_description_en', 'description_en', 'meta_title_en', 'meta_description_en']));
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn(['name_en', 'slug_en', 'description_en', 'content_en', 'meta_title_en', 'meta_description_en']));
    }
};

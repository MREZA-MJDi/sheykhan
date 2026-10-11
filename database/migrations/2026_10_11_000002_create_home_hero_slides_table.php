<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('home_hero_slides', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('slot')->unique();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('mobile_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('title', 120)->nullable();
            $table->text('description')->nullable();
            $table->string('cta_label', 80)->nullable();
            $table->text('cta_url')->nullable();
            $table->unsignedTinyInteger('crop_x')->default(50);
            $table->unsignedTinyInteger('crop_y')->default(46);
            $table->unsignedTinyInteger('mobile_crop_x')->default(54);
            $table->unsignedTinyInteger('mobile_crop_y')->default(50);
            $table->boolean('is_active')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Transfer the currently configured single Hero into the new slide system.
        // The media files and media records remain untouched.
        if (Schema::hasTable('home_hero_settings')) {
            $legacy = DB::table('home_hero_settings')->where('id', 1)->first();

            if ($legacy && ($legacy->media_id || $legacy->mobile_media_id)) {
                DB::table('home_hero_slides')->insert([
                    'slot' => 1,
                    'media_id' => $legacy->media_id,
                    'mobile_media_id' => $legacy->mobile_media_id,
                    'title' => null,
                    'description' => null,
                    'cta_label' => null,
                    'cta_url' => null,
                    'crop_x' => $legacy->crop_x ?? 50,
                    'crop_y' => $legacy->crop_y ?? 46,
                    'mobile_crop_x' => $legacy->mobile_crop_x ?? 54,
                    'mobile_crop_y' => $legacy->mobile_crop_y ?? 50,
                    'is_active' => (bool) $legacy->media_id,
                    'sort_order' => 1,
                    'updated_by' => $legacy->updated_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Ownership is now the slide row; avoid a stale duplicate reference.
                DB::table('home_hero_settings')->where('id', 1)->update([
                    'media_id' => null,
                    'mobile_media_id' => null,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Restore the slot-one image to the legacy settings row before dropping slides.
        if (Schema::hasTable('home_hero_settings') && Schema::hasTable('home_hero_slides')) {
            $slide = DB::table('home_hero_slides')->where('slot', 1)->first();

            if ($slide) {
                DB::table('home_hero_settings')->updateOrInsert(
                    ['id' => 1],
                    [
                        'media_id' => $slide->media_id,
                        'mobile_media_id' => $slide->mobile_media_id,
                        'crop_x' => $slide->crop_x,
                        'crop_y' => $slide->crop_y,
                        'mobile_crop_x' => $slide->mobile_crop_x,
                        'mobile_crop_y' => $slide->mobile_crop_y,
                        'updated_by' => $slide->updated_by,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        }

        Schema::dropIfExists('home_hero_slides');
    }
};

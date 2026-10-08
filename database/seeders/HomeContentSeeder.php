<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\HomeBanner;
use Database\Seeders\Support\SeedMedia;
use Illuminate\Database\Seeder;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        $academy = Academy::where('slug', 'sheykhan-academy')->firstOrFail();
        $ownerId = (int) $academy->owner_id;

        $banners = [
            [
                'slot' => 1,
                'key' => 'learning-path',
                'title' => 'مسیر یادگیری که دیده می‌شود',
                'description' => 'دوره، کلاس، تمرین و آزمون را در یک مسیر منظم دنبال کن و پیشرفتت را قدم‌به‌قدم ببین.',
                'cta_label' => 'مشاهده دوره‌ها',
                'cta_url' => '/courses',
            ],
            [
                'slot' => 2,
                'key' => 'teachers',
                'title' => 'آموزش با مدرس‌هایی که مسیر را می‌شناسند',
                'description' => 'با مدرس‌های تأییدشده شیخان آشنا شو و مسیر آموزشی متناسب با پایه و هدف خودت را پیدا کن.',
                'cta_label' => 'آشنایی با اساتید',
                'cta_url' => '/teachers',
            ],
            [
                'slot' => 3,
                'key' => 'learning-journal',
                'title' => 'یادگیری فقط کلاس نیست',
                'description' => 'از مقاله‌ها، نکته‌های آموزشی و تجربه‌های کاربردی برای بهتر درس خواندن و بهتر نتیجه گرفتن استفاده کن.',
                'cta_label' => 'مجله شیخان',
                'cta_url' => '/blog',
            ],
        ];

        foreach ($banners as $bannerDefinition) {
            $media = SeedMedia::make(
                'home-' . $bannerDefinition['key'],
                $ownerId,
                'image/svg+xml',
                'svg',
                'home-banners',
                'public'
            );

            $academy->media()->syncWithoutDetaching([
                $media->id => [
                    'collection' => 'home-banners',
                    'sort_order' => $bannerDefinition['slot'],
                    'is_featured' => true,
                ],
            ]);

            $banner = HomeBanner::firstOrNew([
                'academy_id' => $academy->id,
                'slot' => $bannerDefinition['slot'],
            ]);

            if (!$banner->exists) {
                $banner->fill([
                    'media_id' => $media->id,
                    'title' => $bannerDefinition['title'],
                    'description' => $bannerDefinition['description'],
                    'cta_label' => $bannerDefinition['cta_label'],
                    'cta_url' => $bannerDefinition['cta_url'],
                    'sort_order' => $bannerDefinition['slot'],
                    'is_active' => true,
                ])->save();
            }
        }
    }
}

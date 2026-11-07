<?php

namespace Database\Seeders;

use App\Models\HeroChallenge;
use Illuminate\Database\Seeder;

final class HeroChallengeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'slug' => 'hero-sequence-doubling',
                'title' => 'الگوی پنهان را پیدا کن',
                'prompt' => 'عدد بعدی این دنباله کدام است؟',
                'sequence' => ['۳', '۶', '۱۲', '۲۴'],
                'explanation' => 'هر عدد دو برابر عدد قبلی است؛ پس ۲۴ دو برابر می‌شود و پاسخ ۴۸ است.',
                'options' => [
                    ['label' => 'A', 'content' => '۳۰', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۳۶', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۴۸', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۴۲', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-sequence-differences',
                'title' => 'اختلاف‌ها را کشف کن',
                'prompt' => 'عدد بعدی این دنباله کدام است؟ به فاصله میان عددها دقت کن.',
                'sequence' => ['۲', '۶', '۱۲', '۲۰'],
                'explanation' => 'اختلاف‌ها به‌ترتیب ۴، ۶، ۸ و ۱۰ هستند؛ بنابراین عدد بعدی ۳۰ است.',
                'options' => [
                    ['label' => 'A', 'content' => '۲۶', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۳۰', 'is_correct' => true],
                    ['label' => 'C', 'content' => '۳۲', 'is_correct' => false],
                    ['label' => 'D', 'content' => '۳۶', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-sequence-squares',
                'title' => 'مربع بعدی را پیدا کن',
                'prompt' => 'کدام عدد جای علامت سؤال قرار می‌گیرد؟',
                'sequence' => ['۱', '۴', '۹', '۱۶'],
                'explanation' => 'این دنباله مربع اعداد طبیعی است: ۱، ۴، ۹، ۱۶ و سپس ۲۵.',
                'options' => [
                    ['label' => 'A', 'content' => '۲۰', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۲۴', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۲۵', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۳۶', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($definitions as $index => $definition) {
            $options = $definition['options'];
            unset($definition['options']);

            $challenge = HeroChallenge::query()->firstOrCreate(
                ['slug' => $definition['slug']],
                $definition + [
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ],
            );

            if (! $challenge->options()->exists()) {
                foreach ($options as $optionIndex => $option) {
                    $challenge->options()->create($option + [
                        'sort_order' => $optionIndex + 1,
                    ]);
                }
            }
        }
    }
}

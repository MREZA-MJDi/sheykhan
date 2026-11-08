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
                'slug' => 'hero-difference-squares',
                'title' => 'ردّ پای مربع‌ها',
                'prompt' => 'عدد بعدی را پیدا کن؛ این بار فقط به خود عددها نگاه نکن، به فاصله‌های میان آن‌ها دقت کن.',
                'sequence' => ['۲', '۶', '۱۵', '۳۱', '۵۶'],
                'explanation' => 'اختلاف‌ها به‌ترتیب ۴، ۹، ۱۶ و ۲۵ هستند؛ یعنی مربع‌های ۲ تا ۵. اختلاف بعدی ۳۶ است، پس ۵۶ + ۳۶ = ۹۲.',
                'options' => [
                    ['label' => 'A', 'content' => '۸۶', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۹۰', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۹۲', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۹۶', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-recursive-three',
                'title' => 'قانون پنهان سه‌مرحله‌ای',
                'prompt' => 'در هر گام، یک عمل ثابت روی عدد قبلی انجام شده است. عدد بعدی چیست؟',
                'sequence' => ['۱', '۴', '۱۳', '۴۰', '۱۲۱'],
                'explanation' => 'هر بار عدد قبلی در ۳ ضرب و سپس ۱ واحد به آن اضافه می‌شود: ۱×۳+۱=۴، ۴×۳+۱=۱۳ و به همین ترتیب؛ پس ۱۲۱×۳+۱ برابر ۳۶۴ است.',
                'options' => [
                    ['label' => 'A', 'content' => '۳۶۱', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۳۶۳', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۳۶۴', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۳۶۶', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-factorial-offset',
                'title' => 'ضرب‌های زنجیره‌ای',
                'prompt' => 'الگو را تا یک گام دیگر ادامه بده. کدام عدد جای علامت سؤال می‌نشیند؟',
                'sequence' => ['۲', '۶', '۲۴', '۱۲۰', '۷۲۰'],
                'explanation' => 'ضریب‌ها به‌ترتیب ۳، ۴، ۵ و ۶ هستند؛ بنابراین گام بعدی ضرب در ۷ است: ۷۲۰×۷ = ۵۰۴۰.',
                'options' => [
                    ['label' => 'A', 'content' => '۴۳۲۰', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۵۰۴۰', 'is_correct' => true],
                    ['label' => 'C', 'content' => '۵۰۴۲', 'is_correct' => false],
                    ['label' => 'D', 'content' => '۵۶۰۰', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-doubling-plus-one',
                'title' => 'دو برابر، اما نه دقیقاً',
                'prompt' => 'یک قانون ساده پشت این دنباله پنهان شده؛ عدد بعدی را حدس بزن.',
                'sequence' => ['۲', '۵', '۱۱', '۲۳', '۴۷'],
                'explanation' => 'هر عدد دو برابر عدد قبلی به‌علاوهٔ یک است؛ ۴۷×۲+۱ برابر ۹۵ می‌شود.',
                'options' => [
                    ['label' => 'A', 'content' => '۹۳', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۹۴', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۹۵', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۹۷', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-growing-differences',
                'title' => 'فاصله‌هایی که رشد می‌کنند',
                'prompt' => 'عدد بعدی چیست؟ فاصلهٔ هر دو عدد متوالی را با فاصلهٔ قبلی مقایسه کن.',
                'sequence' => ['۴', '۷', '۱۳', '۲۵', '۴۹'],
                'explanation' => 'اختلاف‌ها ۳، ۶، ۱۲ و ۲۴ هستند؛ هر اختلاف دو برابر قبلی است. اختلاف بعدی ۴۸ و پاسخ ۹۷ است.',
                'options' => [
                    ['label' => 'A', 'content' => '۹۵', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۹۶', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۹۷', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۹۹', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-cubic-growth',
                'title' => 'جمع مربع‌ها',
                'prompt' => 'این دنباله از جمع‌های معمولی ساخته نشده است. عدد بعدی را پیدا کن.',
                'sequence' => ['۱', '۵', '۱۴', '۳۰', '۵۵'],
                'explanation' => 'اختلاف‌ها ۴، ۹، ۱۶ و ۲۵ هستند؛ مربع‌های ۲ تا ۵. اختلاف بعدی ۳۶ است، پس پاسخ ۹۱ خواهد بود.',
                'options' => [
                    ['label' => 'A', 'content' => '۸۹', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۹۰', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۹۱', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۹۲', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-alternating-operation',
                'title' => 'عملیات تکرارشونده',
                'prompt' => 'در هر مرحله، عدد قبلی دو برابر و سپس دو واحد به آن اضافه شده است. عدد بعدی کدام است؟',
                'sequence' => ['۳', '۸', '۱۸', '۳۸', '۷۸'],
                'explanation' => 'قانون دنباله «ضرب در ۲ و سپس جمع ۲» است؛ بنابراین ۷۸×۲+۲ برابر ۱۵۸ می‌شود.',
                'options' => [
                    ['label' => 'A', 'content' => '۱۵۶', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۱۵۷', 'is_correct' => false],
                    ['label' => 'C', 'content' => '۱۵۸', 'is_correct' => true],
                    ['label' => 'D', 'content' => '۱۶۰', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-prime-indexed',
                'title' => 'جایگاه اعداد اول',
                'prompt' => 'دنباله از اعداد اول تشکیل شده، اما همهٔ اعداد اول را پشت سر هم نمی‌بینی. عدد بعدی چیست؟',
                'sequence' => ['۲', '۵', '۱۱', '۱۷', '۲۳'],
                'explanation' => 'این‌ها اعداد اول در جایگاه‌های ۱، ۳، ۵، ۷ و ۹ هستند؛ عدد اول جایگاه ۱۱ برابر ۳۱ است.',
                'options' => [
                    ['label' => 'A', 'content' => '۲۹', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۳۱', 'is_correct' => true],
                    ['label' => 'C', 'content' => '۳۳', 'is_correct' => false],
                    ['label' => 'D', 'content' => '۳۷', 'is_correct' => false],
                ],
            ],
        ];

        // Keep the original starter questions intact, but move them below the new
        // advanced set so visitors meet the harder challenges first.
        HeroChallenge::query()
            ->whereIn('slug', [
                'hero-sequence-doubling',
                'hero-sequence-differences',
                'hero-sequence-squares',
            ])
            ->update(['sort_order' => 90]);

        $definitions = array_merge([
            [
                'slug' => 'hero-three-mislabeled-boxes',
                'title' => 'سه جعبه با برچسب‌های دروغگو',
                'prompt' => 'سه جعبه داری: یکی فقط سیب، یکی فقط پرتقال و یکی ترکیبی از هر دو. هر سه برچسب اشتباه‌اند. فقط اجازه داری از یک جعبه یک میوه بیرون بیاوری. از کدام جعبه باید انتخاب کنی تا بتوانی محتویات هر سه جعبه را دقیق تشخیص بدهی؟',
                'sequence' => [],
                'explanation' => 'از جعبه‌ای که برچسب «ترکیبی» دارد یک میوه بردار. چون برچسب آن قطعاً غلط است، آن جعبه نمی‌تواند ترکیبی باشد؛ میوه‌ای که بیرون می‌آید نوع واقعی همان جعبه را مشخص می‌کند. سپس با توجه به غلط‌بودن دو برچسب باقی‌مانده، محتویات دو جعبه دیگر هم بدون برداشت اضافه معلوم می‌شود.',
                'options' => [
                    ['label' => 'A', 'content' => 'جعبه‌ای با برچسب سیب', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'جعبه‌ای با برچسب پرتقال', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'جعبه‌ای با برچسب ترکیبی', 'is_correct' => true],
                    ['label' => 'D', 'content' => 'فرقی نمی‌کند؛ هر سه یکسان‌اند', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-two-uneven-ropes',
                'title' => '۴۵ دقیقه با دو طناب نامنظم',
                'prompt' => 'دو طناب داری که هرکدام دقیقاً در ۶۰ دقیقه می‌سوزند؛ اما سرعت سوختن در طول طناب یکنواخت نیست. فقط کبریت داری. چطور دقیقاً ۴۵ دقیقه را اندازه می‌گیری؟',
                'sequence' => [],
                'explanation' => 'طناب اول را از دو سر و طناب دوم را هم‌زمان از یک سر روشن کن. طناب اول پس از ۳۰ دقیقه تمام می‌شود. همان لحظه سر دیگر طناب دوم را هم روشن کن؛ بخش باقی‌مانده آن که معادل ۳۰ دقیقه سوختن از یک سر است، حالا از دو طرف در ۱۵ دقیقه تمام می‌شود. مجموع زمان ۴۵ دقیقه است.',
                'options' => [
                    ['label' => 'A', 'content' => '۳۰ دقیقه', 'is_correct' => false],
                    ['label' => 'B', 'content' => '۴۵ دقیقه', 'is_correct' => true],
                    ['label' => 'C', 'content' => '۵۰ دقیقه', 'is_correct' => false],
                    ['label' => 'D', 'content' => '۶۰ دقیقه', 'is_correct' => false],
                ],
            ],
            [
                'slug' => 'hero-three-switches',
                'title' => 'سه کلید، سه لامپ، یک فرصت',
                'prompt' => 'بیرون اتاق سه کلید و داخل اتاق سه لامپ خاموش داری. هر کلید فقط یکی از لامپ‌ها را روشن می‌کند. فقط یک بار اجازه ورود به اتاق داری. چطور ارتباط هر کلید و لامپ را پیدا می‌کنی؟',
                'sequence' => [],
                'explanation' => 'کلید اول را چند دقیقه روشن کن و سپس خاموشش کن. کلید دوم را روشن بگذار و وارد اتاق شو. لامپ روشن متعلق به کلید دوم است؛ لامپ خاموش اما گرم متعلق به کلید اول و لامپ خاموش و سرد متعلق به کلید سوم است.',
                'options' => [
                    ['label' => 'A', 'content' => 'با توجه به روشنایی هر سه لامپ', 'is_correct' => false],
                    ['label' => 'B', 'content' => 'با روشن‌کردن هم‌زمان هر سه کلید', 'is_correct' => false],
                    ['label' => 'C', 'content' => 'با استفاده از نور و گرمای لامپ‌ها', 'is_correct' => true],
                    ['label' => 'D', 'content' => 'با یک بار خاموش و روشن‌کردن تصادفی', 'is_correct' => false],
                ],
            ],
        ], $definitions);

        foreach ($definitions as $index => $definition) {
            $options = $definition['options'];
            unset($definition['options']);

            $challenge = HeroChallenge::query()->firstOrCreate(
                ['slug' => $definition['slug']],
                $definition + [
                    'is_active' => true,
                    'sort_order' => $index < 3 ? 0 : $index - 2,
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

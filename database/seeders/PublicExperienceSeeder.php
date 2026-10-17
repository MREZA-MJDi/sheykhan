<?php

namespace Database\Seeders;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\AcademyContentCategory;
use App\Models\AcademicGrade;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\HomeBanner;
use App\Models\HomeHeroSlide;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Builds a polished local showcase of the public Sheykhan experience.
 *
 * This intentionally creates demo identities/content and must never run in
 * production. The default DatabaseSeeder invokes it only for APP_ENV=local.
 * It does not create fake purchases, payment transactions, consent records,
 * student results, or claims of real-world admission success.
 */
final class PublicExperienceSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException(
                'PublicExperienceSeeder is local-demo content only. Set APP_ENV=local to run it.'
            );
        }

        // Make the explicitly-invoked seeder self-contained on a fresh local DB.
        $this->call([
            RolePermissionSeeder::class,
            AcademicReferenceSeeder::class,
        ]);

        [$academy, $owner] = $this->ensureAcademyAndOwner();
        $faculty = $this->seedFacultyArtwork($academy, $owner);

        // Seed public catalog content only; never create demo learners, class
        // rosters, student progress, payments, orders, or fake achievement claims.
        $this->seedShowcaseCourses($academy, $owner, $faculty);
        $this->seedHeroAndBanners($academy, $owner);
        $this->seedCourseArtwork($academy, $owner);
        $this->seedEditorialContent($academy, $owner);
        $this->seedAcademyArticles($academy, $owner);
        $this->seedStoreCatalog();

        $this->clearPublicCaches();
    }

    /**
     * Reuse the academy's existing owner. On an empty local DB, create only
     * the minimum owner/academy foundation required for public content.
     *
     * @return array{0: Academy, 1: User}
     */
    private function ensureAcademyAndOwner(): array
    {
        $academy = Academy::query()->where('slug', 'sheykhan-academy')->first();
        $owner = $academy && $academy->owner_id
            ? User::withTrashed()->find($academy->owner_id)
            : null;

        if (! $owner) {
            $owner = User::withTrashed()->where('email', 'owner@sheykhan.test')->first();

            if (! $owner) {
                $owner = User::query()->create([
                    'name' => 'مدیر آکادمی شیخان',
                    'email' => 'owner@sheykhan.test',
                    'mobile' => '09100000001',
                    'password' => Hash::make((string) env('SEED_USER_PASSWORD', 'Sheykhan@12345')),
                    'status' => 'active',
                ]);
            } elseif ($owner->trashed()) {
                $owner->restore();
            }

            if (! $academy) {
                $academy = Academy::query()->create([
                    'owner_id' => $owner->id,
                    'name' => 'آکادمی شیخان',
                    'slug' => 'sheykhan-academy',
                    'code' => 'SHK-001',
                    'description' => 'آکادمی آموزشی شیخان؛ آموزش مفهومی، تمرین هدفمند و مسیر روشن رشد.',
                    'phone' => '02191000000',
                    'email' => 'academy@sheykhan.test',
                    'city' => 'تهران',
                    'province' => 'تهران',
                    'status' => 'active',
                ]);
            }
        } elseif ($owner->trashed()) {
            $owner->restore();
        }

        $owner->roles()->syncWithoutDetaching([
            Role::query()->where('slug', 'academy-owner')->value('id'),
        ]);

        $academy->users()->syncWithoutDetaching([
            $owner->id => [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        return [$academy, $owner];
    }

    private function seedHeroAndBanners(Academy $academy, User $owner): void
    {
        $slides = [
            [
                'slot' => 1,
                'art' => 'hero-curiosity',
                'title' => 'آینده از یک سؤال شروع می‌شود',
                'description' => 'کنجکاوی را به فهم عمیق تبدیل کن؛ با درس‌های هدفمند، تمرین‌های هوشمند و قدم‌هایی که تو را جلو می‌برند.',
                'cta_label' => 'کشف دوره‌ها',
                'cta_url' => '/courses',
                'crop_x' => 68,
                'crop_y' => 50,
                'mobile_crop_x' => 72,
                'mobile_crop_y' => 48,
            ],
            [
                'slot' => 2,
                'art' => 'hero-mastery',
                'title' => 'فقط درس نخوان؛ مسلط شو',
                'description' => 'مفهوم را یاد بگیر، آموخته‌هایت را به کار ببر و با بازخورد بهتر بفهم قدم بعدی چیست.',
                'cta_label' => 'آشنایی با مدرس‌ها',
                'cta_url' => '/teachers',
                'crop_x' => 70,
                'crop_y' => 48,
                'mobile_crop_x' => 68,
                'mobile_crop_y' => 48,
            ],
            [
                'slot' => 3,
                'art' => 'hero-possibility',
                'title' => 'مسیر رشدت را خودت بساز',
                'description' => 'از اولین درس تا چالش‌های جدی‌تر، یک فضای یکپارچه برای یادگیری، تمرین و پیشرفت در اختیار توست.',
                'cta_label' => 'شروع مسیر یادگیری',
                'cta_url' => '/courses',
                'crop_x' => 68,
                'crop_y' => 50,
                'mobile_crop_x' => 70,
                'mobile_crop_y' => 50,
            ],
        ];

        foreach ($slides as $definition) {
            $image = $this->image($definition['art'], (int) $owner->id, 'home-hero');
            $slide = HomeHeroSlide::query()->firstOrNew(['slot' => $definition['slot']]);
            $currentMedia = $slide->exists && $slide->media_id
                ? Media::query()->find($slide->media_id)
                : null;

            // Preserve owner-uploaded art and copy. Replace only an empty slot
            // or a known generated placeholder from a previous local seeding.
            $maySeed = ! $slide->exists
                || ! $slide->media_id
                || data_get($currentMedia?->metadata, 'seeded') === true;

            if (! $maySeed) {
                continue;
            }

            $slide->fill([
                'media_id' => $image->id,
                'title' => $definition['title'],
                'description' => $definition['description'],
                'cta_label' => $definition['cta_label'],
                'cta_url' => $definition['cta_url'],
                'crop_x' => $definition['crop_x'],
                'crop_y' => $definition['crop_y'],
                'mobile_crop_x' => $definition['mobile_crop_x'],
                'mobile_crop_y' => $definition['mobile_crop_y'],
                'is_active' => true,
                'sort_order' => $definition['slot'],
                'updated_by' => $owner->id,
            ])->save();
        }

        $banners = [
            [
                'slot' => 1,
                'art' => 'banner-learning-path',
                'title' => 'یادگیری که به حرکت تبدیل می‌شود',
                'description' => 'دوره، درس، تمرین و پیگیری پیشرفت را در یک مسیر منظم کنار هم ببین.',
                'cta_label' => 'مشاهده دوره‌ها',
                'cta_url' => '/courses',
            ],
            [
                'slot' => 2,
                'art' => 'banner-faculty',
                'title' => 'از مدرس‌های خوب، بهتر یاد بگیر',
                'description' => 'با تخصص و مسیر آموزشی مدرس‌ها آشنا شو و نقطه شروع خودت را پیدا کن.',
                'cta_label' => 'آشنایی با مدرس‌ها',
                'cta_url' => '/teachers',
            ],
            [
                'slot' => 3,
                'art' => 'banner-journal',
                'title' => 'یادگیری، بیرون از کلاس هم ادامه دارد',
                'description' => 'ایده‌ها، روش‌های مطالعه و نکته‌های قابل‌اجرا را در مجله شیخان کشف کن.',
                'cta_label' => 'خواندن مجله',
                'cta_url' => '/blog',
            ],
        ];

        foreach ($banners as $definition) {
            $image = $this->image($definition['art'], (int) $owner->id, 'home-banners');
            $banner = HomeBanner::query()->firstOrNew([
                'academy_id' => $academy->id,
                'slot' => $definition['slot'],
            ]);
            $currentMedia = $banner->exists && $banner->media_id
                ? Media::query()->find($banner->media_id)
                : null;

            $maySeed = ! $banner->exists
                || ! $banner->media_id
                || data_get($currentMedia?->metadata, 'seeded') === true;

            if (! $maySeed) {
                continue;
            }

            $banner->fill([
                'media_id' => $image->id,
                'title' => $definition['title'],
                'description' => $definition['description'],
                'cta_label' => $definition['cta_label'],
                'cta_url' => $definition['cta_url'],
                'sort_order' => $definition['slot'],
                'crop_x' => 50,
                'crop_y' => 50,
                'is_active' => true,
            ])->save();
        }
    }

    private function seedCourseArtwork(Academy $academy, User $owner): void
    {
        $courses = [
            'math-foundation-7' => [
                'art' => 'course-math-lab',
                'short_description' => 'از نسبت و تناسب تا عبارت جبری؛ ریاضی را با مثال، استدلال و تمرین مرحله‌به‌مرحله بفهم.',
                'description' => 'یک مسیر مفهومی برای پایه هفتم: ایده اصلی هر مبحث را کشف کن، راه‌حل را با مثال بررسی کن و با تمرین‌های تدریجی به تسلط برس. درس‌ها، تکالیف و خودسنجی در همین مسیر کنار هم قرار گرفته‌اند.',
            ],
            'science-foundation-8' => [
                'art' => 'course-science-lab',
                'short_description' => 'علم را از دل پدیده‌های روزمره کشف کن؛ از انرژی و نیرو تا آزمایش و توضیح علمی.',
                'description' => 'در این مسیر علوم را فقط حفظ نمی‌کنی. مشاهده می‌کنی، پیش‌بینی می‌سازی، دلیل می‌آوری و بعد با تمرین و پرسش بررسی می‌کنی که چرا پدیده‌ها اتفاق می‌افتند.',
            ],
            'gifted-prep-9' => [
                'art' => 'course-thinking-lab',
                'short_description' => 'برای سؤال‌های چالشی آماده شو؛ الگوها را پیدا کن و راهبرد حل مسئله بساز.',
                'description' => 'این مسیر برای تقویت استدلال و حل مسئله طراحی شده است: سؤال را تحلیل کن، الگوها را تشخیص بده، گزینه‌ها را با دلیل بررسی کن و بعد از هر تمرین، روش فکرکردنت را مرور کن.',
            ],
        ];

        foreach ($courses as $slug => $definition) {
            $course = Course::query()
                ->where('academy_id', $academy->id)
                ->where('slug', $slug)
                ->first();

            if (! $course) {
                continue;
            }

            $course->forceFill([
                'short_description' => $definition['short_description'],
                'description' => $definition['description'],
            ])->save();

            $this->attachArtwork(
                $course,
                $this->image($definition['art'], (int) $owner->id, 'course-covers'),
                'featured',
                null,
            );
        }
    }

    private function seedFacultyArtwork(User $owner): void
    {
        $faculty = [
            'teacher.math@sheykhan.test' => [
                'art' => 'teacher-math',
                'specialization' => 'ریاضی و تفکر تحلیلی',
                'bio' => 'کمک می‌کنم دانش‌آموز به‌جای حفظ‌کردن فرمول، منطق پشت مسئله را کشف کند و راه‌حلش را با اطمینان توضیح بدهد.',
            ],
            'teacher.science@sheykhan.test' => [
                'art' => 'teacher-science',
                'specialization' => 'علوم تجربی و یادگیری مفهومی',
                'bio' => 'از پرسش‌های روزمره شروع می‌کنیم، فرضیه می‌سازیم و با مشاهده و استدلال به پاسخ نزدیک می‌شویم.',
            ],
            'teacher.gifted@sheykhan.test' => [
                'art' => 'teacher-thinking',
                'specialization' => 'استدلال و آمادگی تیزهوشان',
                'bio' => 'تمرکز کلاس بر دیدن الگوها، انتخاب راهبرد و توضیح شفاف مسیر حل مسئله است؛ نه فقط رسیدن به جواب.',
            ],
        ];

        foreach ($faculty as $email => $definition) {
            $teacher = User::query()->where('email', $email)->first();

            if (! $teacher) {
                continue;
            }

            $profile = TeacherProfile::query()->updateOrCreate(
                ['user_id' => $teacher->id],
                [
                    'specialization' => $definition['specialization'],
                    'bio' => $definition['bio'],
                    'education' => 'مدرس آکادمی شیخان',
                    'experience_years' => 7,
                    'is_verified' => true,
                    'is_public' => true,
                ],
            );

            $this->attachArtwork(
                $profile,
                $this->image($definition['art'], (int) $owner->id, 'teacher-avatars'),
                'teacher-avatar',
                'teacher-avatar',
            );
        }
    }

    private function seedEditorialContent(Academy $academy, User $owner): void
    {
        $category = BlogCategory::query()->updateOrCreate(
            ['slug' => 'learning'],
            ['name' => 'یادگیری و رشد', 'description' => 'راهنماهای کاربردی برای مطالعه، تمرین و رشد ذهنی'],
        );

        $tagDefinitions = [
            'study-skills' => 'مهارت مطالعه',
            'gifted' => 'تفکر و تیزهوشان',
            'family-learning' => 'همراهی خانواده',
        ];

        $tags = collect($tagDefinitions)->map(
            fn (string $name, string $slug) => BlogTag::query()->firstOrCreate(['slug' => $slug], ['name' => $name])
        );

        $posts = [
            [
                'slug' => 'effective-study-plan',
                'title' => 'برنامه مطالعه‌ای که واقعاً قابل اجراست',
                'excerpt' => 'برنامه خوب قرار نیست هر دقیقه را پُر کند؛ باید شروع را آسان، تمرین را مشخص و مرور را قابل اندازه‌گیری کند.',
                'content' => "از یک هدف کوچک و روشن شروع کن. به‌جای «امروز ریاضی می‌خوانم»، بنویس «دو مثال نسبت را حل می‌کنم و یک راه‌حل را با صدای بلند توضیح می‌دهم». هدف واضح، شروع‌کردن را ساده‌تر می‌کند.\n\nمطالعه را به بازه‌های کوتاه تقسیم کن: ابتدا مفهوم را بخوان، سپس بدون نگاه‌کردن به پاسخ تمرین کن و در پایان اشتباهت را بررسی کن. صرفاً دوباره‌خوانی‌کردن، حس آشنایی می‌دهد اما لزوماً نشان نمی‌دهد که مطلب را می‌توانی به‌تنهایی به کار ببری.\n\nبرای مرور، فاصله بگذار. همان روز نکته‌های دشوار را کوتاه مرور کن، روز بعد یک سؤال مشابه حل کن و چند روز بعد دوباره به مسئله برگرد. اگر پاسخ را فراموش کردی، آن را نشانه شکست نبین؛ دقیقاً همان‌جاست که مرور ارزش پیدا می‌کند.\n\nدر پایان هر جلسه سه چیز را ثبت کن: چه چیزی را فهمیدم؟ کجا گیر کردم؟ قدم بعدی چیست؟ همین سه سؤال ساده، برنامه مطالعه را از یک فهرست بلند به مسیری قابل پیگیری تبدیل می‌کند.",
                'art' => 'journal-focus',
                'tags' => ['study-skills'],
            ],
            [
                'slug' => 'gifted-exam-strategy',
                'title' => 'وقتی سؤال سخت است، اول روش فکرکردنت را عوض کن',
                'excerpt' => 'در سؤال‌های چالشی، عجله برای محاسبه معمولاً کمک نمی‌کند. مسئله را بازنویسی کن، الگوها را پیدا کن و گزینه‌ها را با دلیل بسنج.',
                'content' => "اول صورت سؤال را با کلمات خودت بازگو کن. دقیقاً چه چیزی داده شده و چه چیزی باید پیدا شود؟ اگر نتوانی این دو را از هم جدا کنی، احتمال دارد محاسبه را از مسیر اشتباه شروع کنی.\n\nبعد از خودت بپرس آیا الگو، تقارن، حذف گزینه یا یک حالت ساده‌تر می‌تواند راه را روشن کند. در مسئله‌های چندمرحله‌ای، یک مثال کوچک بساز و ببین رابطه‌ها چگونه تغییر می‌کنند.\n\nوقتی گزینه‌ای به نظرت درست آمد، دنبال دلیل رد گزینه‌های دیگر هم بگرد. پاسخ درست فقط عدد نهایی نیست؛ روشی است که بتوانی آن را توضیح بدهی و در مسئله‌ای تازه دوباره استفاده کنی.\n\nبعد از تمرین، اشتباه را دسته‌بندی کن: بدفهمی صورت سؤال، انتخاب راهبرد نامناسب، خطای محاسبه یا کمبود زمان. هرکدام تمرین متفاوتی می‌خواهد. با این روش، هر سؤال به داده‌ای برای بهترشدن تبدیل می‌شود.",
                'art' => 'journal-thinking',
                'tags' => ['gifted', 'study-skills'],
            ],
            [
                'slug' => 'parent-learning-support',
                'title' => 'چطور کنار فرزندمان باشیم، بدون اینکه جای او درس بخوانیم؟',
                'excerpt' => 'حمایت مؤثر همیشه به معنی توضیح بیشتر نیست؛ گاهی یک سؤال خوب، فضای آرام و توجه به تلاش، از چند جواب آماده مفیدتر است.',
                'content' => "به‌جای اینکه فقط بپرسیم «چند گرفتی؟»، درباره مسیر سؤال کنیم: «امروز کدام بخش روشن‌تر شد؟ کجا نیاز به کمک داشتی؟» این گفت‌وگو توجه را از نتیجه لحظه‌ای به مهارت و فرایند یادگیری منتقل می‌کند.\n\nبرای تمرین، زمان و فضای قابل پیش‌بینی بسازید، اما تمام برنامه را به‌جای فرزندتان مدیریت نکنید. بگذارید خودش هدف جلسه را انتخاب کند و در پایان درباره آنچه انجام شده گزارش کوتاهی بدهد.\n\nوقتی اشتباه می‌کند، قبل از نشان‌دادن پاسخ، از او بخواهید راه‌حلش را توضیح دهد. ممکن است یک برداشت نادرست مهم آشکار شود که با تصحیح فوری عدد نهایی دیده نمی‌شد.\n\nدر نهایت، تلاش مشخص را تحسین کنید: پشتکار، پرسیدن سؤال، اصلاح روش یا بازگشت به تمرین دشوار. پیام اصلی این است که توانایی با تمرین و بازخورد رشد می‌کند و لازم نیست همه‌چیز در اولین تلاش کامل باشد.",
                'art' => 'journal-family',
                'tags' => ['family-learning', 'study-skills'],
            ],
        ];

        foreach ($posts as $index => $definition) {
            $post = BlogPost::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'category_id' => $category->id,
                    'author_id' => $owner->id,
                    'title' => $definition['title'],
                    'excerpt' => $definition['excerpt'],
                    'content' => $definition['content'],
                    'status' => 'published',
                    'published_at' => now()->subDays(2 - $index),
                ],
            );

            $post->tags()->syncWithoutDetaching(
                $tags->whereIn('slug', $definition['tags'])->pluck('id')->all()
            );

            $this->attachArtwork(
                $post,
                $this->image($definition['art'], (int) $owner->id, 'editorial-covers'),
                'featured',
                'featured',
            );
        }
    }

    private function seedAcademyArticles(Academy $academy, User $owner): void
    {
        $groups = [
            'parents' => [
                'title' => 'سخنی با اولیاء',
                'art' => 'journal-family',
                'articles' => [
                    ['support', 'محیطی بسازیم که سؤال پرسیدن در آن امن باشد', 'کودک زمانی راحت‌تر فکر می‌کند که بداند برای پرسیدن و اشتباه‌کردن سرزنش نمی‌شود.', "برای یادگیری، خانه لازم نیست شبیه کلاس درس باشد. یک زمان نسبتاً ثابت، ابزارهای ساده و فرصتی برای گفت‌وگو کافی است. وقتی فرزندتان به مسئله‌ای پاسخ اشتباه می‌دهد، پیش از گفتن جواب بپرسید: «از کجا شروع کردی؟» همین سؤال کمک می‌کند مسیر فکرش را ببیند و نقطه نیازمند اصلاح را پیدا کند."],
                    ['routine', 'چک‌لیست کوتاه خانواده برای پایان هفته', 'یک مرور آرام هفتگی از فشار آوردن برای ساعت‌های بیشتر درس‌خواندن مفیدتر است.', "در پایان هفته از فرزندتان بخواهید یک موفقیت، یک دشواری و یک هدف کوچک برای هفته آینده انتخاب کند. برنامه را با ظرفیت واقعی او هماهنگ کنید و زمانی هم برای استراحت و فعالیت بیرون از درس در نظر بگیرید. پیوستگی مهم‌تر از برنامه‌ای سنگین است که چند روز بیشتر دوام نمی‌آورد."],
                ],
            ],
            'students' => [
                'title' => 'سخنی با دانش‌آموزان',
                'art' => 'course-math-lab',
                'articles' => [
                    ['active-recall', 'آزمون گرفتن از خودت، قبل از آزمون اصلی', 'کتاب را ببند و ببین چه چیزی را می‌توانی بدون کمک توضیح بدهی.', "بعد از مطالعه، صفحه را ببند و مفهوم اصلی را با زبان خودت شرح بده. سپس دو یا سه پرسش کوتاه حل کن و جواب را با راه‌حل مقایسه کن. اگر گیر کردی، فقط همان بخش را دوباره بخوان و یک بار دیگر بدون نگاه‌کردن تلاش کن. این چرخه، هم نقطه قوت را روشن می‌کند و هم نشان می‌دهد وقت مرور را کجا صرف کنی."],
                    ['mistakes', 'دفتر اشتباهات؛ راهی برای هوشمندانه‌تر تمرین کردن', 'اشتباه ثبت‌شده می‌تواند برای جلسه بعدی دقیقاً بگوید چه چیزی را تمرین کنی.', "برای هر اشتباه، صورت کوتاه سؤال، علت خطا و یک نکته برای دفعه بعد را بنویس. هفته آینده به همان سؤال برگرد و آن را از ابتدا حل کن. هدف پرکردن دفتر نیست؛ هدف آن است که یک خطا بارها تکرار نشود و روش حل مسئله‌ات دقیق‌تر شود."],
                ],
            ],
            'gifted' => [
                'title' => 'سلام تیزهوشان',
                'art' => 'course-thinking-lab',
                'articles' => [
                    ['patterns', 'قبل از محاسبه، دنبال الگو بگرد', 'گاهی یک تغییر زاویه نگاه چند خط محاسبه را حذف می‌کند.', "در دنباله‌ها، شکل‌ها و مسئله‌های منطقی، ابتدا رابطه میان اجزا را توصیف کن. یک حالت ساده‌تر بساز، تفاوت دو مرحله را مقایسه کن و بعد حدس خودت را با حالت بعدی آزمایش کن. اگر الگو تنها در یک مثال درست باشد، هنوز اثبات نشده است."],
                    ['explain', 'راه‌حل درست باید قابل توضیح باشد', 'اگر بتوانی بگویی چرا هر مرحله لازم است، فهم تو از جواب نهایی فراتر رفته است.', "پس از حل مسئله، کنار هر مرحله بنویس از کدام داده یا قاعده استفاده کرده‌ای. سپس ببین آیا راه کوتاه‌تری وجود دارد و آیا روش تو برای یک نمونه متفاوت هم کار می‌کند. توضیح‌دادن راه‌حل، ابهام‌ها را زودتر از پاسخ‌نامه آشکار می‌کند."],
                ],
            ],
            'foreign-resources' => [
                'title' => 'نکاتی از سوالات منابع خارجی',
                'art' => 'journal-thinking',
                'articles' => [
                    ['read-deeply', 'صورت سؤال را دقیق بخوان؛ حتی واژه‌های کوچک مهم‌اند', 'در مسائل مفهومی، قیدها و اطلاعات ظاهراً فرعی ممکن است مسیر پاسخ را عوض کنند.', "پیش از انتخاب روش، فعل سؤال را مشخص کن: توضیح بده، مقایسه کن، تخمین بزن یا محاسبه کن؟ سپس داده‌ها را جدا از نتیجه موردنظر یادداشت کن. در مسائل چندمرحله‌ای، پاسخ هر مرحله را با شرط‌های اولیه تطبیق بده تا خطایی زودتر دیده شود."],
                    ['reasoning', 'جواب را با شاهد پشتیبانی کن', 'یک پاسخ قوی فقط انتخاب گزینه نیست؛ رابطه بین شواهد و نتیجه را هم روشن می‌کند.', "پس از رسیدن به پاسخ، یک جمله بنویس که نشان دهد کدام داده یا اصل علمی از آن پشتیبانی می‌کند. اگر سؤال چند پاسخ نزدیک دارد، معیار مقایسه را صریح کن. این تمرین در علوم، ریاضی و درک مطلب به یک اندازه کاربرد دارد."],
                ],
            ],
            'question-designer' => [
                'title' => 'اگر من طراح سؤال بودم',
                'art' => 'course-science-lab',
                'articles' => [
                    ['make-question', 'یک سؤال خوب چه چیزی را می‌سنجد؟', 'سؤال مناسب دانش‌آموز را وادار می‌کند مفهوم را به کار بگیرد، نه اینکه فقط جمله‌ای را به خاطر بیاورد.', "برای طراحی سؤال، ابتدا مهارت هدف را تعیین کن. سپس یک موقعیت روشن بساز، اطلاعات کافی بده و از دانش‌آموز بخواه دلیلش را نشان دهد. در پایان بررسی کن آیا سؤال واقعاً همان مهارت را می‌سنجد یا به دلیل ابهام کلامی، چیز دیگری را آزمایش می‌کند."],
                    ['distractors', 'گزینه‌های انحرافی باید از یک خطای قابل‌فهم بیایند', 'گزینه نادرست زمانی آموزنده است که نشان بدهد کدام برداشت اشتباه ممکن است رخ دهد.', "به‌جای گزینه‌های بی‌ربط، خطاهای معمول را شناسایی کن؛ مثلاً جابه‌جاکردن نسبت، نادیده‌گرفتن واحد یا اجرای نادرست ترتیب عملیات. پاسخ‌نامه بعدی باید روشن کند چرا هر گزینه درست یا نادرست است. این کار آزمون را به ابزاری برای یادگیری تبدیل می‌کند."],
                ],
            ],
        ];

        foreach ($groups as $categorySlug => $definition) {
            $category = AcademyContentCategory::query()->updateOrCreate(
                ['academy_id' => $academy->id, 'slug' => $categorySlug],
                [
                    'title' => $definition['title'],
                    'sort_order' => array_search($categorySlug, array_keys($groups), true) + 1,
                    'is_active' => true,
                ],
            );

            $art = $this->image($definition['art'], (int) $owner->id, 'academy-content-covers');

            foreach ($definition['articles'] as $index => [$slug, $title, $excerpt, $body]) {
                $article = AcademyContent::query()->updateOrCreate(
                    ['academy_id' => $academy->id, 'slug' => 'showcase-' . $categorySlug . '-' . $slug],
                    [
                        'category_id' => $category->id,
                        'type' => 'article',
                        'title' => $title,
                        'excerpt' => $excerpt,
                        'body' => $body,
                        'status' => 'published',
                        'is_featured' => $index === 0,
                        'sort_order' => $index,
                        'published_at' => now()->subDays($index + 1),
                        'created_by' => $owner->id,
                    ],
                );

                $this->attachArtwork($article, $art, 'cover', 'cover');
            }
        }
    }

    private function seedStoreCatalog(): void
    {
        $categories = [
            'books' => ['کتابخانه', 'راهنماهایی برای عمیق‌تر فکرکردن و یادگیری مستقل.', 1],
            'booklets' => ['جزوه‌های آموزشی', 'مرورهای منظم برای تمرین و تثبیت مفاهیم.', 2],
            'exams' => ['آزمون و تمرین', 'تمرین هدفمند برای دیدن نقطه قوت و قدم بعدی.', 3],
        ];

        foreach ($categories as $slug => [$name, $description, $sortOrder]) {
            ProductCategory::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'sort_order' => $sortOrder, 'is_active' => true],
            );
        }

        $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();
        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();

        $products = [
            [
                'slug' => 'study-rhythm-workbook',
                'category' => 'booklets',
                'title' => 'دفتر کار ریتم مطالعه',
                'subtitle' => 'یک برنامه قابل اجرا برای تمرکز، تمرین و مرور',
                'description' => 'یک ورک‌بوک کوتاه و کاربردی با نقشه هفتگی، چرخه مطالعه فعال و پرسش‌های بازبینی. فایل همراه محصول یک نسخه تمرینی انگلیسی از محتوای آموزشی است و از بخش فایل‌های محصول قابل مدیریت است.',
                'type' => 'booklet',
                'price' => 125000,
                'art' => 'journal-focus',
                'pdf_title' => 'STUDY RHYTHM WORKBOOK',
                'pdf_lines' => [
                    'A practical workbook for focused study and steady progress.',
                    '',
                    'THE 25-MINUTE LEARNING LOOP',
                    '1. Pick one specific outcome for this session.',
                    '2. Study one concept for 10 minutes.',
                    '3. Close the material and recall the idea from memory.',
                    '4. Solve a fresh problem without looking at the answer.',
                    '5. Write one question to revisit tomorrow.',
                    '',
                    'WEEKLY REVIEW',
                    'What became clearer this week?',
                    'Which mistake taught me something useful?',
                    'What is my smallest next step?',
                    '',
                    'MY NEXT SESSION',
                    'Goal: __________________________________________',
                    'Practice task: __________________________________',
                    'One thing to review: ____________________________',
                ],
            ],
            [
                'slug' => 'math-patterns-workbook',
                'category' => 'books',
                'title' => 'کارگاه الگوهای ریاضی',
                'subtitle' => 'تمرین دیدن رابطه‌ها، توضیح راه‌حل و بررسی جواب',
                'description' => 'مجموعه‌ای کوتاه برای تمرین نسبت، دنباله و استدلال مرحله‌به‌مرحله. فایل همراه محصول شامل تمرین و پاسخ‌نامه مختصر انگلیسی است؛ محتوای محصول در پنل قابل جایگزینی است.',
                'type' => 'book',
                'price' => 185000,
                'art' => 'course-math-lab',
                'pdf_title' => 'MATH PATTERN WORKSHOP',
                'pdf_lines' => [
                    'Notice the relationship. Explain the rule. Check the result.',
                    '',
                    'CHALLENGE 1 - SEQUENCES',
                    'Complete: 2, 5, 8, 11, __, __',
                    'Explain the rule in one sentence.',
                    '',
                    'CHALLENGE 2 - RATIOS',
                    'Four notebooks cost 120 units.',
                    'How much do seven notebooks cost at the same rate?',
                    'Show the unit price before you calculate.',
                    '',
                    'CHALLENGE 3 - ALGEBRA',
                    'If 3x + 4 = 19, find x and check your answer.',
                    'Write one line explaining why your first step works.',
                    '',
                    'QUICK ANSWERS',
                    'Challenge 1: 14, 17; add 3 each time.',
                    'Challenge 2: 210 units; one notebook costs 30 units.',
                    'Challenge 3: x = 5; substitute to check 15 + 4 = 19.',
                    '',
                    'REFLECTION: Which rule was easiest to explain?',
                ],
            ],
            [
                'slug' => 'science-observation-lab',
                'category' => 'exams',
                'title' => 'آزمایشگاه مشاهده و پرسش',
                'subtitle' => 'از یک مشاهده ساده تا نتیجه‌ای که بتوانی از آن دفاع کنی',
                'description' => 'یک برگه کار آزمایشگاهی برای ساختن سؤال، پیش‌بینی، ثبت داده و توضیح نتیجه. فایل همراه محصول یک فعالیت کوتاه انگلیسی است و می‌تواند از پنل با جزوه نهایی جایگزین شود.',
                'type' => 'exam',
                'price' => 95000,
                'art' => 'course-science-lab',
                'pdf_title' => 'OBSERVE AND ASK - MINI LAB',
                'pdf_lines' => [
                    'A short worksheet for curious minds.',
                    '',
                    'STEP 1 - OBSERVE',
                    'Choose a paper plane, a rolling object, or a shadow.',
                    'Write three things you can observe without guessing.',
                    '',
                    'STEP 2 - ASK A TESTABLE QUESTION',
                    'What single change might affect the result?',
                    '',
                    'STEP 3 - MAKE A PREDICTION',
                    'I predict that _________________________________',
                    'because ________________________________________',
                    '',
                    'STEP 4 - TEST FAIRLY',
                    'Change one thing. Keep other conditions similar.',
                    'Repeat the test three times and record what happens.',
                    '',
                    'STEP 5 - CONCLUDE',
                    'What did the evidence show?',
                    'Was the prediction supported? What would you test next?',
                    '',
                    'A strong conclusion connects an observation to evidence.',
                ],
            ],
        ];

        foreach ($products as $definition) {
            $category = ProductCategory::query()->where('slug', $definition['category'])->firstOrFail();
            $product = Product::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'academy_id' => $academy->id,
                    'category_id' => $category->id,
                    'created_by' => $owner->id,
                    'title' => $definition['title'],
                    'subtitle' => $definition['subtitle'],
                    'description' => $definition['description'],
                    'product_type' => $definition['type'],
                    'delivery_type' => 'download',
                    'price' => $definition['price'],
                    'currency' => 'IRR',
                    'status' => 'published',
                    'is_featured' => true,
                    'published_at' => now()->subDays(2),
                ],
            );

            $cover = $this->image($definition['art'], (int) $owner->id, 'product-covers');
            $this->attachArtwork($product, $cover, 'featured', null);

            $pdf = $this->privateWorkbook(
                $definition['slug'],
                (int) $owner->id,
                $definition['pdf_title'],
                $definition['pdf_lines'],
            );

            ProductFile::query()->firstOrCreate(
                ['product_id' => $product->id, 'media_id' => $pdf->id],
                [
                    'version' => '1.0',
                    'is_primary' => true,
                    'is_preview' => false,
                    'requires_watermark' => true,
                ],
            );
        }
    }

    private function privateWorkbook(string $slug, int $ownerId, string $title, array $lines): Media
    {
        $disk = Storage::disk('local');
        $path = 'showcase-workbooks/' . $slug . '.pdf';
        $contents = $this->workbookPdf($title, $lines);

        if (! $disk->put($path, $contents)) {
            throw new RuntimeException('Could not write demo workbook: ' . $path);
        }

        return Media::query()->updateOrCreate(
            ['disk' => 'local', 'path' => $path],
            [
                'uploaded_by' => $ownerId,
                'original_name' => $slug . '.pdf',
                'file_name' => $slug . '.pdf',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size' => strlen($contents),
                'checksum' => hash('sha256', $contents),
                'visibility' => 'private',
                'collection' => 'products',
                'metadata' => ['seeded' => true, 'seeded_public_experience' => true, 'local_demo_workbook' => true],
                'status' => 'active',
            ],
        );
    }

    /** Create a valid, readable one-page PDF containing useful sample exercises. */
    private function workbookPdf(string $title, array $lines): string
    {
        $escape = static fn (string $value): string => str_replace(
            ['\\\\', '(', ')'],
            ['\\\\\\\\', '\\(', '\\)'],
            $value,
        );

        $stream = "BT\n/F1 10 Tf\n52 752 Td\n(SHEYKHAN LEARNING LAB) Tj\n";
        $stream .= "/F1 20 Tf\n0 -34 Td\n(" . $escape($title) . ") Tj\n";
        $stream .= "/F1 10 Tf\n0 -27 Td\n";
        $stream .= "(" . $escape('An original local-demo practice sheet. Work it out, then explain why.') . ") Tj\n0 -24 Td\n";

        foreach ($lines as $line) {
            $line = (string) $line;
            if ($line === '') {
                $stream .= "0 -7 Td\n";
                continue;
            }

            foreach (explode("\n", wordwrap($line, 82, "\n", true)) as $wrapped) {
                $stream .= "(" . $escape($wrapped) . ") Tj\n0 -15 Td\n";
            }
        }

        $stream .= "ET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($index = 1; $index <= 5; $index++) {
            $pdf .= str_pad((string) $offsets[$index], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }

        return $pdf . "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
    }

    private function image(string $key, int $ownerId, string $collection): Media
    {
        // Public media is served through the application's guarded media route,
        // so the showcase does not depend on a storage:link symlink existing.
        $disk = Storage::disk('local');
        $path = 'showcase-artwork/' . $key . '.svg';
        $contents = $this->artworkSvg($key);

        if (! $disk->put($path, $contents)) {
            throw new RuntimeException('Could not write showcase image: ' . $path);
        }

        return Media::query()->updateOrCreate(
            ['disk' => 'local', 'path' => $path],
            [
                'uploaded_by' => $ownerId,
                'original_name' => $key . '.svg',
                'file_name' => $key . '.svg',
                'mime_type' => 'image/svg+xml',
                'extension' => 'svg',
                'size' => strlen($contents),
                'checksum' => hash('sha256', $contents),
                'visibility' => 'public',
                'collection' => $collection,
                'metadata' => [
                    'seeded' => true,
                    'seeded_public_experience' => true,
                    'editable_from_admin' => true,
                    'art_direction' => 'original-local-vector',
                ],
                'status' => 'active',
            ],
        );
    }

    /**
     * Attach generated cover art only when no owner-uploaded public image exists.
     * A previous generated placeholder is replaced; real owner media is preserved.
     */
    private function attachArtwork(
        Model $model,
        Media $media,
        string $pivotCollection,
        ?string $scopeCollection,
    ): void {
        $query = $model->media()
            ->where('media.visibility', 'public')
            ->where('media.status', 'active');

        if ($scopeCollection !== null) {
            $query->wherePivot('collection', $scopeCollection);
        }

        $existing = $query->get();
        $ownerImage = $existing->first(
            fn (Media $item) => data_get($item->metadata, 'seeded') !== true
        );

        if ($ownerImage) {
            return;
        }

        foreach ($existing as $item) {
            if (data_get($item->metadata, 'seeded') === true) {
                $model->media()->detach($item->id);
            }
        }

        $model->media()->syncWithoutDetaching([
            $media->id => [
                'collection' => $pivotCollection,
                'sort_order' => 0,
                'is_featured' => true,
            ],
        ]);
    }

    private function artworkSvg(string $key): string
    {
        $themes = [
            'hero-curiosity' => ['#10162f', '#6654dc', '#47d6bf', '#ffbe79', 'CURIOUS MINDS', 'ASK. EXPLORE. GROW.'],
            'hero-mastery' => ['#101d37', '#3579cb', '#5ce2bf', '#ffcc73', 'THE LEARNING LAB', 'THINK DEEPER. GO FURTHER.'],
            'hero-possibility' => ['#20123f', '#a05cf1', '#47d8db', '#ff9e73', 'FUTURE IN MOTION', 'YOUR NEXT CHAPTER STARTS HERE.'],
            'banner-learning-path' => ['#192344', '#7064e9', '#63d6c0', '#ffc17d', 'LEARN WITH PURPOSE', 'LESSON  ·  PRACTICE  ·  PROGRESS'],
            'banner-faculty' => ['#132b3c', '#3a8eae', '#75e1c1', '#f7c56d', 'PEOPLE WHO INSPIRE', 'GOOD QUESTIONS MAKE GREAT MINDS.'],
            'banner-journal' => ['#211c49', '#9364e9', '#ffb66e', '#62d7c1', 'THE LEARNING JOURNAL', 'SMALL IDEAS. BIG DISCOVERIES.'],
            'course-math-lab' => ['#261c55', '#8b73f3', '#58dbc0', '#ffb76d', 'MATH / THINKING', 'NOTICE THE PATTERN. PROVE THE IDEA.'],
            'course-science-lab' => ['#103b4a', '#2ba8a2', '#9ce1bd', '#ffcf7e', 'SCIENCE / INQUIRY', 'OBSERVE. QUESTION. DISCOVER.'],
            'course-thinking-lab' => ['#311d50', '#d073a8', '#84bbff', '#ffd17c', 'REASONING / CHALLENGE', 'TRAIN YOUR MIND TO SEE MORE.'],
            'journal-focus' => ['#201b46', '#8475ed', '#6bd9c3', '#ffbe7c', 'STUDY SMARTER', 'FOCUS ON THE NEXT SMALL STEP.'],
            'journal-thinking' => ['#152a48', '#4f83dc', '#83e1ce', '#ffca70', 'THINKING TOOLS', 'A BETTER QUESTION CHANGES EVERYTHING.'],
            'journal-family' => ['#27324f', '#7965df', '#52cbb4', '#ffb7a1', 'GROW TOGETHER', 'CURIOSITY NEEDS ROOM TO BREATHE.'],
            'teacher-math' => ['#6d5be5', '#372d77', '#b6adff', '#ffd2a1', 'MATH'],
            'teacher-science' => ['#159d95', '#15576d', '#9be9cd', '#ffcf9d', 'SCIENCE'],
            'teacher-thinking' => ['#d8799b', '#6f3d91', '#eeb8d1', '#ffdaa4', 'REASONING'],
        ];

        [$background, $primary, $secondary, $accent, $headline, $tagline] = $themes[$key]
            ?? ['#202846', '#7969e8', '#58d5c0', '#ffc17d', 'SHEYKHAN', 'LEARN WITH PURPOSE'];

        if (str_starts_with($key, 'teacher-')) {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" role="img" aria-labelledby="t d">
<title id="t">Illustrated faculty portrait — {$headline}</title>
<desc id="d">Original abstract illustrated portrait for the local Sheykhan learning showcase.</desc>
<defs>
 <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$background}"/><stop offset="1" stop-color="{$primary}"/></linearGradient>
 <linearGradient id="shirt" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$secondary}"/><stop offset="1" stop-color="{$accent}"/></linearGradient>
 <radialGradient id="halo"><stop stop-color="#fff" stop-opacity=".32"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
</defs>
<rect width="600" height="600" fill="url(#bg)"/>
<circle cx="300" cy="246" r="214" fill="url(#halo)"/>
<circle cx="300" cy="246" r="184" fill="none" stroke="#fff" stroke-opacity=".17" stroke-width="2"/>
<circle cx="300" cy="246" r="155" fill="none" stroke="#fff" stroke-opacity=".12" stroke-width="2" stroke-dasharray="5 11"/>
<path d="M72 620c14-138 91-210 228-210s214 72 228 210" fill="url(#shirt)"/>
<path d="M221 385h158l36 71-115 93-115-93Z" fill="#fff" fill-opacity=".21"/>
<path d="M236 356h128v75c-31 29-97 29-128 0Z" fill="#d7a78d"/>
<ellipse cx="300" cy="260" rx="108" ry="129" fill="#e8bc9d"/>
<path d="M191 258c-18-91 21-166 110-174 94-9 132 69 109 172l-22-52c-42 4-97-25-122-55-14 35-40 57-71 70Z" fill="#262841"/>
<path d="M205 237c-6-65 22-116 62-139 52-30 113-2 130 48-47 5-91-13-121-35-13 48-37 83-71 126Z" fill="{$primary}"/>
<path d="M238 257q20-15 40 0M323 257q20-15 40 0" stroke="#44334b" stroke-width="7" fill="none" stroke-linecap="round"/>
<path d="M280 310q20 18 40 0" stroke="#a45b67" stroke-width="6" fill="none" stroke-linecap="round"/>
<path d="M266 345q34 27 68 0" stroke="#fff" stroke-opacity=".85" stroke-width="5" fill="none" stroke-linecap="round"/>
<circle cx="143" cy="165" r="6" fill="{$accent}"/><circle cx="466" cy="210" r="8" fill="{$secondary}"/>
<path d="M115 312l9 18 20 3-15 13 4 20-18-10-18 10 4-20-15-13 20-3Z" fill="{$accent}" fill-opacity=".9"/>
<rect x="34" y="34" width="532" height="532" rx="28" fill="none" stroke="#fff" stroke-opacity=".25" stroke-width="2"/>
<text x="300" y="532" fill="#fff" font-family="Arial,sans-serif" font-size="22" font-weight="800" letter-spacing="4" text-anchor="middle">SHEYKHAN FACULTY</text>
<text x="300" y="562" fill="#fff" fill-opacity=".78" font-family="Arial,sans-serif" font-size="14" font-weight="700" letter-spacing="3" text-anchor="middle">{$headline}</text>
</svg>
SVG;
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675" role="img" aria-labelledby="t d">
<title id="t">{$headline} — Sheykhan Learning</title>
<desc id="d">Original vivid abstract education artwork created for the local Sheykhan showcase.</desc>
<defs>
 <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$background}"/><stop offset=".56" stop-color="{$primary}"/><stop offset="1" stop-color="{$background}"/></linearGradient>
 <linearGradient id="page" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#fff9ed"/><stop offset="1" stop-color="#d8e5ff"/></linearGradient>
 <linearGradient id="page2" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#fdf0db"/><stop offset="1" stop-color="#ddccff"/></linearGradient>
 <radialGradient id="glow"><stop stop-color="{$secondary}" stop-opacity=".6"/><stop offset="1" stop-color="{$secondary}" stop-opacity="0"/></radialGradient>
 <pattern id="grid" width="34" height="34" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.5" fill="#fff" fill-opacity=".17"/></pattern>
 <filter id="shadow" x="-30%" y="-30%" width="160%" height="170%"><feGaussianBlur in="SourceAlpha" stdDeviation="13"/><feOffset dy="16"/><feComponentTransfer><feFuncA type="linear" slope=".28"/></feComponentTransfer><feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge></filter>
</defs>
<rect width="1200" height="675" fill="url(#bg)"/>
<rect width="1200" height="675" fill="url(#grid)"/>
<circle cx="915" cy="300" r="310" fill="url(#glow)"/>
<circle cx="918" cy="300" r="258" fill="none" stroke="#fff" stroke-opacity=".22" stroke-width="2"/>
<circle cx="918" cy="300" r="202" fill="none" stroke="#fff" stroke-opacity=".18" stroke-width="2" stroke-dasharray="4 12"/>
<circle cx="918" cy="300" r="151" fill="none" stroke="{$accent}" stroke-opacity=".75" stroke-width="3"/>
<path d="M710 120c89-60 182-67 280-18s132 132 140 231" fill="none" stroke="{$secondary}" stroke-width="5" stroke-linecap="round" stroke-opacity=".7"/>
<path d="M691 512c97-75 205-92 302-45s151 105 180 156" fill="none" stroke="{$accent}" stroke-width="4" stroke-linecap="round" stroke-opacity=".65"/>
<g filter="url(#shadow)" transform="translate(708 174) rotate(-7 180 140)">
 <path d="M0 37c87-43 173-40 259 12v245C176 248 89 247 0 294Z" fill="url(#page)" stroke="#fff" stroke-opacity=".8" stroke-width="3"/>
 <path d="M259 49c87-47 174-55 263-12v245c-88-40-175-36-263 12Z" fill="url(#page2)" stroke="#fff" stroke-opacity=".8" stroke-width="3"/>
 <path d="M259 49v245" stroke="{$primary}" stroke-opacity=".55" stroke-width="5"/>
 <path d="M33 82c55-21 121-20 185 2M32 115c57-20 121-18 186 3M32 148c57-17 121-16 186 5M32 181c54-13 122-12 186 6" fill="none" stroke="{$primary}" stroke-opacity=".54" stroke-width="7" stroke-linecap="round"/>
 <path d="M294 91c58-20 121-27 190-13M294 125c64-19 125-23 190-8M294 159c64-16 124-19 190-4M294 193c63-15 124-16 190-1" fill="none" stroke="{$background}" stroke-opacity=".5" stroke-width="7" stroke-linecap="round"/>
 <path d="M44 228c60-10 116-6 173 9M295 229c64-11 124-6 190 6" fill="none" stroke="{$secondary}" stroke-width="8" stroke-linecap="round"/>
</g>
<g transform="translate(880 82) rotate(15)">
 <rect width="34" height="206" rx="12" fill="{$accent}"/>
 <rect x="5" y="12" width="24" height="156" rx="8" fill="#fff3cb" fill-opacity=".7"/>
 <path d="M0 184 17 226 34 184Z" fill="#e9c6a0"/>
 <path d="m12 211 5 15 5-15Z" fill="{$background}"/>
</g>
<g fill="{$accent}">
 <path d="M1051 110l9 18 20 3-15 13 4 20-18-10-18 10 4-20-15-13 20-3Z"/>
 <circle cx="1107" cy="456" r="6"/><circle cx="723" cy="110" r="5"/>
</g>
<g fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="3">
 <path d="M611 112v26m-13-13h26"/><path d="M1098 552v24m-12-12h24"/>
</g>
<text x="68" y="91" fill="#fff" fill-opacity=".76" font-family="Arial,sans-serif" font-size="17" letter-spacing="5" font-weight="700">S H E Y K H A N  /  L E A R N I N G  L A B</text>
<text x="68" y="490" fill="#fff" font-family="Arial,sans-serif" font-size="39" font-weight="900" letter-spacing="1">{$headline}</text>
<text x="68" y="528" fill="#fff" fill-opacity=".83" font-family="Arial,sans-serif" font-size="17" font-weight="600" letter-spacing="1">{$tagline}</text>
<rect x="68" y="566" width="106" height="5" rx="3" fill="{$secondary}"/>
<rect x="184" y="566" width="52" height="5" rx="3" fill="{$accent}"/>
<text x="68" y="625" fill="#fff" fill-opacity=".56" font-family="Arial,sans-serif" font-size="12" letter-spacing="4">CURIOSITY  /  PRACTICE  /  GROWTH</text>
</svg>
SVG;
    }

    private function clearPublicCaches(): void
    {
        foreach ([
            'public:home:data:v3',
            'public:home:data:v4',
            'public:home:data:v5',
            'public:platform:stats:v1',
            'public:home:courses:3',
            'public:home:courses:6',
            'public:home:teachers:4:v2',
            'public:home:teachers:6:v2',
            'public:home:blog:latest:3:v1',
            'public:home:product-categories',
            'public:home:products:3',
            'public:home:academy-content:v2:3',
            'public:home:achievements:6',
            'public:home:testimonials:6',
            'public:seo:sitemap:v1',
        ] as $key) {
            Cache::forget($key);
        }

        app(\App\Services\HomeBannerService::class)->forgetCache();
        app(\App\Services\HomeHeroService::class)->forgetCache();
        app(\App\Services\CourseCatalogService::class)->clearPublicCache();
        app(\App\Services\TeacherDirectoryService::class)->clearPublicCache();
        app(\App\Services\BlogService::class)->clearPublicCache();
        app(\App\Services\AcademyContentService::class)->clearPublicCache();
    }
}

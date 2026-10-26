<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\FinancialTransaction;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerBlogFinanceSeoIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_specialist_has_a_separate_global_seo_workspace_while_owner_has_no_seo_access(): void
    {
        [$owner, $academy] = $this->ownerWithPermissions([
            'dashboard.view', 'seo.manage', 'academy.view', 'blog.manage', 'content.manage',
        ]);

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره SEO آزمایشی',
            'slug' => 'seo-course-' . Str::random(7),
            'short_description' => 'توضیح کوتاه',
            'description' => 'محتوای تست',
            'level' => 'متوسط',
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'duration_minutes' => 30,
            'published_at' => now()->subMinute(),
        ]);

        $post = BlogPost::query()->create([
            'author_id' => $owner->id,
            'title' => 'مقاله SEO آزمایشی',
            'slug' => 'seo-post-' . Str::random(7),
            'content' => 'محتوای تست',
            'status' => 'draft',
        ]);

        $category = $academy->academyContentCategories()->create([
            'title' => 'محتوای SEO',
            'slug' => 'seo-content-' . Str::random(7),
            'is_active' => true,
        ]);

        $item = $academy->academyContents()->create([
            'category_id' => $category->id,
            'type' => 'article',
            'title' => 'محتوای آموزشگاه SEO',
            'slug' => 'seo-academy-content-' . Str::random(7),
            'body' => 'محتوای تست',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'created_by' => $owner->id,
        ]);

        foreach ([
            route('owner.blog.index'),
            route('owner.blog.create'),
            route('owner.blog.edit', $post),
            route('owner.content.index'),
            route('owner.content.create'),
            route('owner.content.edit', $item),
            route('owner.website.index'),
        ] as $url) {
            $this->actingAs($owner)->get($url)->assertForbidden();
        }

        $this->actingAs($owner)->get(route('owner.dashboard'))
            ->assertOk()
            ->assertDontSee('SEO و دیده‌شدن')
            ->assertDontSee(route('owner.seo.index'), false);

        try {
            app(\App\Services\OwnerBlogService::class)->index($owner);
            $this->fail('Direct blog service access must not bypass the SEO-only role.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        foreach ([
            route('owner.seo.index'),
            route('owner.seo.edit', ['course', $course->id]),
            route('owner.seo.edit', ['blog', $post->id]),
            route('owner.seo.edit', ['content', $item->id]),
            route('owner.seo.edit', ['academy', $academy->id]),
        ] as $url) {
            $this->actingAs($owner)->get($url)->assertForbidden();
        }

        $seoSpecialist = $this->seoSpecialist();

        foreach ([
            route('owner.blog.index'),
            route('owner.blog.create'),
            route('owner.blog.edit', $post),
            route('owner.content.index'),
            route('owner.content.create'),
            route('owner.content.edit', $item),
            route('owner.website.index'),
        ] as $url) {
            $this->actingAs($seoSpecialist)->get($url)->assertOk();
        }
        $this->actingAs($seoSpecialist)
            ->get(route('owner.seo.index'))
            ->assertOk()
            ->assertSee('مرکز کنترل SEO شیخان')
            ->assertSee('دوره SEO آزمایشی');

        $this->actingAs($seoSpecialist)
            ->get(route('owner.seo.edit', ['course', $course->id]))
            ->assertOk();

        $this->actingAs($seoSpecialist)
            ->patch(route('owner.seo.update', ['course', $course->id]), [
                'title' => 'عنوان SEO اختصاصی',
                'description' => 'توضیح صفحه',
                'robots' => 'index,follow',
            ])
            ->assertRedirect(route('owner.seo.index'));

        $this->assertDatabaseHas('seo_metas', [
            'seoable_type' => Course::class,
            'seoable_id' => $course->id,
            'title' => 'عنوان SEO اختصاصی',
        ]);
    }

        public function test_public_blog_is_managed_globally_by_seo_specialist_not_academy_owner(): void
    {
        [$ownerA] = $this->ownerWithPermissions(['blog.manage', 'seo.manage']);
        [$ownerB] = $this->ownerWithPermissions(['blog.manage']);

        $ownPost = BlogPost::create([
            'author_id' => $ownerA->id,
            'title' => 'مقاله من',
            'slug' => 'my-post-' . Str::random(6),
            'content' => 'متن',
            'status' => 'draft',
        ]);

        $otherPost = BlogPost::create([
            'author_id' => $ownerB->id,
            'title' => 'مقاله دیگر',
            'slug' => 'other-post-' . Str::random(6),
            'content' => 'متن دیگر',
            'status' => 'draft',
        ]);

        $this->actingAs($ownerA)->get(route('owner.blog.edit', $ownPost))->assertForbidden();
        $this->actingAs($ownerA)->get(route('owner.blog.edit', $otherPost))->assertForbidden();

        $seo = $this->seoSpecialist();
        $this->actingAs($seo)->get(route('owner.blog.edit', $ownPost))->assertOk();
        $this->actingAs($seo)->get(route('owner.blog.edit', $otherPost))->assertOk();

        $this->actingAs($ownerA)
            ->patch(route('owner.seo.update', ['blog', $ownPost->id]), [
                'title' => 'SEO مقاله من',
                'description' => 'توضیح SEO',
                'robots' => 'index,follow',
                'schema_json' => '{"@context":"https://schema.org","@type":"Article"}',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('seo_metas', [
            'seoable_type' => BlogPost::class,
            'seoable_id' => $ownPost->id,
        ]);

        $this->actingAs($ownerA)
            ->patch(route('owner.seo.update', ['blog', $otherPost->id]), [
                'title' => 'نباید ثبت شود',
                'robots' => 'index,follow',
            ])
            ->assertForbidden();
    }

        public function test_owner_cannot_manage_seo_for_owned_or_foreign_products(): void
    {
        [$ownerA, $academyA] = $this->ownerWithPermissions(['seo.manage']);
        [, $academyB] = $this->ownerWithPermissions();

        $category = ProductCategory::create([
            'name' => 'منابع',
            'slug' => 'seo-products-' . Str::random(6),
            'description' => 'محصولات آموزشی',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $myProduct = Product::create([
            'academy_id' => $academyA->id,
            'category_id' => $category->id,
            'created_by' => $ownerA->id,
            'title' => 'محصول متعلق به من',
            'slug' => 'my-seo-product-' . Str::random(8),
            'product_type' => 'digital',
            'delivery_type' => 'download',
            'price' => 100000,
            'currency' => 'IRR',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $otherProduct = Product::create([
            'academy_id' => $academyB->id,
            'category_id' => $category->id,
            'title' => 'محصول متعلق به مالک دیگر',
            'slug' => 'other-seo-product-' . Str::random(8),
            'product_type' => 'digital',
            'delivery_type' => 'download',
            'price' => 200000,
            'currency' => 'IRR',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->actingAs($ownerA)
            ->get(route('owner.seo.edit', ['product', $myProduct->id]))
            ->assertForbidden();

        $this->actingAs($ownerA)
            ->patch(route('owner.seo.update', ['product', $myProduct->id]), [
                'title' => 'عنوان SEO محصول من',
                'description' => 'توضیح اختصاصی محصول',
                'robots' => 'index,follow',
            ])
            ->assertForbidden();

        $this->actingAs($ownerA)
            ->get(route('owner.seo.edit', ['product', $otherProduct->id]))
            ->assertForbidden();

        $this->assertDatabaseMissing('seo_metas', [
            'seoable_type' => Product::class,
            'seoable_id' => $myProduct->id,
        ]);
    }

        public function test_owner_finance_page_exposes_only_owned_academies(): void
    {
        [$ownerA, $academyA] = $this->ownerWithPermissions(['finance.view']);
        [$ownerB, $academyB] = $this->ownerWithPermissions(['finance.view']);

        FinancialTransaction::create([
            'academy_id' => $academyA->id,
            'user_id' => $ownerA->id,
            'type' => 'enrollment_payment',
            'status' => 'completed',
            'amount' => 1250000,
            'currency' => 'IRT',
            'reference' => 'a-' . Str::random(6),
            'occurred_at' => now(),
        ]);

        FinancialTransaction::create([
            'academy_id' => $academyB->id,
            'user_id' => $ownerB->id,
            'type' => 'enrollment_payment',
            'status' => 'completed',
            'amount' => 9900000,
            'currency' => 'IRT',
            'reference' => 'b-' . Str::random(6),
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($ownerA)
            ->get(route('owner.finance.index'))
            ->assertOk();

        $response->assertSee(\App\Support\PersianUi::number(1250000));
        $response->assertDontSee(\App\Support\PersianUi::number(9900000));
    }

    public function test_owner_can_manage_owned_academy_content_but_not_its_seo_metadata(): void
    {
        [$owner, $academy] = $this->ownerWithPermissions(['content.manage', 'seo.manage']);

        $category = $academy->academyContentCategories()->create([
            'title' => 'آزمون',
            'slug' => 'tests',
            'is_active' => true,
        ]);

        $content = $academy->academyContents()->create([
            'category_id' => $category->id,
            'type' => 'article',
            'title' => 'آزمون آزمایشی',
            'slug' => 'trial-test',
            'body' => 'متن',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.seo.update', ['content', $content->id]), [
                'title' => 'آزمون آزمایشی | SEO',
                'description' => 'توضیح متا',
                'robots' => 'index,follow',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('seo_metas', [
            'seoable_type' => $content->getMorphClass(),
            'seoable_id' => $content->id,
        ]);

        $this->get(route('academy.content.show', [$academy, $content]))
            ->assertOk()
            ->assertSee('آزمون آزمایشی', false);
    }

        private function seoSpecialist(): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'seo-specialist'],
            ['name' => 'متخصص SEO', 'description' => 'مدیریت SEO صفحات عمومی سایت']
        );

        $permissionIds = [];
        foreach (['dashboard.view', 'seo.manage'] as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['label' => $name, 'group' => explode('.', $name)[0]]
            );
            $permissionIds[] = $permission->id;
        }
        $role->permissions()->syncWithoutDetaching($permissionIds);

        $user = User::factory()->create([
            'name' => 'متخصص SEO',
            'email' => Str::random(8) . '@seo.test',
        ]);
        $user->roles()->attach($role->id);

        return $user;
    }

    private function ownerWithPermissions(array $permissions = []): array
    {
        $role = Role::firstOrCreate(
            ['slug' => 'academy-owner'],
            ['name' => 'مدیر آموزشگاه', 'description' => 'Owner']
        );

        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['label' => $name, 'group' => 'owner']
            );
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $owner = User::factory()->create([
            'email' => Str::random(8) . '@owner.test',
        ]);
        $owner->roles()->attach($role->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی مالک',
            'slug' => 'owner-academy-' . Str::random(7),
            'status' => 'active',
        ]);

        return [$owner, $academy];
    }
}

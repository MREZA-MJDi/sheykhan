<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\BlogPost;
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

    public function test_owner_can_manage_own_blog_post_but_not_another_owner_post(): void
    {
        [$ownerA, $academyA] = $this->ownerWithPermissions(['blog.manage', 'seo.manage']);
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

        $this->actingAs($ownerA)
            ->get(route('owner.blog.edit', $ownPost))
            ->assertOk();

        $this->actingAs($ownerA)
            ->get(route('owner.blog.edit', $otherPost))
            ->assertNotFound();

        $this->actingAs($ownerA)
            ->patch(route('owner.seo.update', ['blog', $ownPost->id]), [
                'title' => 'SEO مقاله من',
                'description' => 'توضیح SEO',
                'robots' => 'index,follow',
                'schema_json' => '{"@context":"https://schema.org","@type":"Article"}',
            ])
            ->assertRedirect(route('owner.seo.index'));

        $this->assertDatabaseHas('seo_metas', [
            'seoable_type' => BlogPost::class,
            'seoable_id' => $ownPost->id,
            'title' => 'SEO مقاله من',
        ]);

        $this->actingAs($ownerA)
            ->patch(route('owner.seo.update', ['blog', $otherPost->id]), [
                'title' => 'نباید ثبت شود',
                'robots' => 'index,follow',
            ])
            ->assertNotFound();
    }


    public function test_owner_can_manage_seo_for_owned_product_but_not_another_owners_product(): void
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
            ->patch(route('owner.seo.update', ['product', $myProduct->id]), [
                'title' => 'عنوان SEO محصول من',
                'description' => 'توضیح اختصاصی محصول',
                'robots' => 'index,follow',
            ])
            ->assertRedirect(route('owner.seo.index'));

        $this->assertDatabaseHas('seo_metas', [
            'seoable_type' => Product::class,
            'seoable_id' => $myProduct->id,
            'title' => 'عنوان SEO محصول من',
        ]);

        $this->actingAs($ownerA)
            ->get(route('owner.seo.edit', ['product', $otherProduct->id]))
            ->assertNotFound();

        $this->actingAs($ownerA)
            ->patch(route('owner.seo.update', ['product', $otherProduct->id]), [
                'title' => 'تغییر غیرمجاز',
                'robots' => 'index,follow',
            ])
            ->assertNotFound();
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

        $response->assertSee('1,250,000');
        $response->assertDontSee('9,900,000');
    }

    public function test_owner_can_manage_seo_for_owned_academy_content(): void
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
            ->assertRedirect(route('owner.seo.index'));

        $this->assertDatabaseHas('seo_metas', [
            'seoable_type' => $content->getMorphClass(),
            'seoable_id' => $content->id,
            'title' => 'آزمون آزمایشی | SEO',
        ]);

        $this->get(route('academy.content.show', [$academy, $content]))
            ->assertOk()
            ->assertSee('آزمون آزمایشی | SEO', false);
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

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Blog\StoreBlogPostRequest;
use App\Http\Requests\Owner\Blog\UpdateBlogPostRequest;
use App\Models\BlogPost;
use App\Services\OwnerBlogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class BlogController extends Controller
{
    public function index(OwnerBlogService $blog): View { return view('seo.blog.index', $blog->index(request()->user())); }

    public function create(OwnerBlogService $blog): View {
        return view('seo.blog.form', ['post'=>new BlogPost(['status'=>'draft']), ...$blog->formData(request()->user())]);
    }

    public function store(StoreBlogPostRequest $request, OwnerBlogService $blog): RedirectResponse {
        $post=$blog->create($request->user(),$request->validated());
        return redirect()->route('seo.blog.edit',$post)->with('success','مقاله با موفقیت ساخته شد.');
    }

    public function edit(BlogPost $post, OwnerBlogService $blog): View {
        $post=$blog->owned(request()->user(),$post);
        return view('seo.blog.form',['post'=>$post,...$blog->formData(request()->user())]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $post, OwnerBlogService $blog): RedirectResponse {
        $post=$blog->update($request->user(),$post,$request->validated());
        return redirect()->route('seo.blog.edit',$post)->with('success','مقاله به‌روزرسانی شد.');
    }
    
    public function destroy(\Illuminate\Http\Request $request, BlogPost $post, OwnerBlogService $blog): RedirectResponse
    {
        $blog->delete($request->user(), $post);

        return redirect()->route('seo.blog.index')->with('success', 'مقاله حذف شد.');
    }
}

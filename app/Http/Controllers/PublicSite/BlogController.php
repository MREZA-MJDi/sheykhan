<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(BlogService $service): View
    {
        return view('pages.blog.index', [
            'posts' => $service->paginate(),
        ]);
    }

    public function show(string $slug, BlogService $service): View
    {
        return view('pages.blog.show', [
            'post' => $service->findPublished($slug),
        ]);
    }
}

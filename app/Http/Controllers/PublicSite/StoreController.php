<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request, ProductCatalogService $catalog): View
    {
        return view('pages.store.index', [
            'products' => $catalog->paginate($request->string('category')->toString() ?: null),
            'categories' => $catalog->categories(),
            'activeCategory' => $request->string('category')->toString(),
        ]);
    }
}

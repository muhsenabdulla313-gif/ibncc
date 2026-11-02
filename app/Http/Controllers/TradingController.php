<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
    use App\Models\Category;
use App\Models\Banner;
class TradingController extends Controller
{

public function index()
{

 
    $banners = Banner::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

    $categories = Category::whereNull('parent_id')
        ->where('is_active', true)
        ->with([
            'children' => function ($query) {
                $query->with('children');
            }
        ])
        ->orderBy('sort_order')
        ->get();

    return view('trading', compact('categories','banners'));
}
}

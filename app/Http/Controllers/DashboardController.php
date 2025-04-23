<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        // Gather summary stats
        $totalPosts = Post::where('type', 'post')->count();
        $totalPages = Post::where('type', 'page')->count();
        $totalCategories = Category::count();
        $totalProducts = Product::count();

        // Fetch the 5 most recent posts
        $recentPosts = Post::where('type', 'post')
            ->latest()
            ->limit(5)
            ->get();

        // Render the dashboard.index view
        return view('dashboard.index', compact(
            'totalPosts',
            'totalPages',
            'totalCategories',
            'totalProducts',
            'recentPosts'
        ));
    }
}
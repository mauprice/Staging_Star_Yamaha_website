<?php

namespace App\Http\Controllers;

use App\Models\YamahaNews;

class NewsController extends Controller
{
    public function index()
    {
        $news = YamahaNews::active()
            ->where(function ($query) {
                $query->whereNull('type')->orWhere('type', 'not like', 'Outboard%');
            })
            ->orderBy('sort_index')
            ->orderByDesc('id')
            ->get();

        $types = $news->pluck('type')->unique()->filter()->sort()->values();

        return view('yamaha.news.index', compact('news', 'types'));
    }

    public function show(int $id, string $slug)
    {
        $article = YamahaNews::where('id', $id)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('type')->orWhere('type', 'not like', 'Outboard%');
            })
            ->firstOrFail();

        return view('yamaha.news.show', compact('article'));
    }
}

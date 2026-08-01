<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::with(['author', 'category'])
            ->published()
            ->latest('published_at');

        $category = null;
        if ($slug = $request->query('category')) {
            $category = Category::where('slug', $slug)->firstOrFail();
            $query->where('category_id', $category->id);
        }

        $posts = $query->paginate(9)->withQueryString();

        return view('frontend.blog.index', compact('posts', 'category'));
    }

    public function byCategory(Category $category): View
    {
        $posts = Post::with(['author'])
            ->published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(9);

        return view('frontend.blog.index', [
            'posts' => $posts,
            'category' => $category,
        ]);
    }

    public function search(Request $request): View
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);
        $keyword = $request->query('q');

        $posts = Post::with(['author', 'category'])
            ->published()
            ->where(function ($q) use ($keyword) {
                $locale = app()->getLocale();
                $q->whereRaw("LOWER(JSON_EXTRACT(title, '$.\"{$locale}\"')) LIKE ?", ['%' . strtolower($keyword) . '%'])
                  ->orWhereRaw("LOWER(JSON_EXTRACT(body, '$.\"{$locale}\"')) LIKE ?", ['%' . strtolower($keyword) . '%']);
            })
            ->latest('published_at')
            ->paginate(9);

        return view('frontend.blog.search', compact('posts', 'keyword'));
    }

    public function show(string $locale, string $slug): View
    {
        // Karena pakai prefix locale, $locale akan di segment(1)
        $post = Post::with(['author', 'category', 'comments.user'])
            ->published()
            ->whereRaw("LOWER(JSON_EXTRACT(slug, '$.\"{$locale}\"')) = ?", [strtolower($slug)])
            ->firstOrFail();

        $post->incrementViews();

        $related = Post::with(['author'])
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn($q) => $q->where('category_id', $post->category_id))
            ->limit(3)
            ->get();

        return view('frontend.blog.show', compact('post', 'related'));
    }

    public function showDirect(string $slug): View
    {
        $locale = app()->getLocale();
        return $this->show($locale, $slug);
    }

    /** ============================================================
     *  RSS Feed
     *  ============================================================ */
    public function feed(): Response
    {
        $posts = Post::with(['author'])
            ->published()
            ->latest('published_at')
            ->limit(20)
            ->get();

        $content = view('frontend.feed', ['posts' => $posts, 'locale' => app()->getLocale()])->render();

        return response($content, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }

    /** ============================================================
     *  Sitemap.xml
     *  ============================================================ */
    public function sitemap(): Response
    {
        $posts = Post::with([])->published()->get();
        $content = view('frontend.sitemap', ['posts' => $posts])->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}

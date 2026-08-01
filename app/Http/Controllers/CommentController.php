<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, string $locale, string $slug): RedirectResponse
    {
        $post = Post::with([])->published()
            ->whereRaw("LOWER(JSON_EXTRACT(slug, '$.\"{$locale}\"')) = ?", [strtolower($slug)])
            ->firstOrFail();

        if (!$post->allow_comments) {
            abort(403, 'Komentar dimatikan untuk postingan ini.');
        }

        $validated = $request->validate([
            'body'      => 'required|string|min:3|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'post_id'   => $post->id,
            'user_id'   => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'body'      => strip_tags($validated['body']),
            'status'    => 'approved',
        ]);

        return back()->with('success', __('Komentar terkirim, Bos! Tinggal di-moderasi admin kalo perlu.'));
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === Auth::id() || Auth::user()?->hasAnyRole(['super-admin', 'admin', 'editor']), 403);
        $comment->delete();

        return back()->with('success', 'Komentar dihapus.');
    }
}

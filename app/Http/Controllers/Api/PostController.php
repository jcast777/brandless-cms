<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['author', 'category', 'tags', 'media'])
            ->published()
            ->orderBy('published_at', 'desc');

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        $posts = $query->paginate($request->get('per_page', 15));

        return response()->json($posts);
    }

    public function featured(): JsonResponse
    {
        $posts = Post::with(['author', 'category', 'tags', 'media'])
            ->published()
            ->featured()
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($posts);
    }

    public function show(string $slug): JsonResponse
    {
        $post = Post::with(['author', 'category', 'tags', 'media'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment view count
        $post->increment('views_count');

        return response()->json($post);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'in:draft,published,scheduled,archived',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'meta' => 'nullable|array',
            'seo' => 'nullable|array',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'category_id' => $request->category_id,
            'status' => $request->status ?? 'draft',
            'published_at' => $request->published_at,
            'author_id' => Auth::id(),
            'is_featured' => $request->is_featured ?? false,
            'allow_comments' => $request->allow_comments ?? true,
            'meta' => $request->meta,
            'seo' => $request->seo,
        ]);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load(['author', 'category', 'tags']), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        // Check if user can update this post
        if (!Auth::user()->hasRole('admin') && $post->author_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'status' => 'in:draft,published,scheduled,archived',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'meta' => 'nullable|array',
            'seo' => 'nullable|array',
        ]);

        $post->update($request->only([
            'title', 'content', 'excerpt', 'category_id', 'status',
            'published_at', 'is_featured', 'allow_comments', 'meta', 'seo'
        ]));

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return response()->json($post->load(['author', 'category', 'tags']));
    }

    public function destroy(string $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        // Check if user can delete this post
        if (!Auth::user()->hasRole('admin') && $post->author_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}

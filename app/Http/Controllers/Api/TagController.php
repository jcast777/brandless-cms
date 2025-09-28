<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    /**
     * Display a listing of tags.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tags = Tag::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $tags,
            'message' => 'Tags retrieved successfully.'
        ]);
    }

    /**
     * Store a newly created tag in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tag = Tag::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
            'is_active' => $request->boolean('is_active', true),
            'meta' => $request->meta ?? [],
        ]);

        return response()->json([
            'data' => $tag,
            'message' => 'Tag created successfully.'
        ], 201);
    }

    /**
     * Display the specified tag by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $slug)
    {
        $tag = Tag::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'data' => $tag,
            'message' => 'Tag retrieved successfully.'
        ]);
    }

    /**
     * Get posts for a specific tag.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function posts(string $slug)
    {
        $tag = Tag::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $posts = $tag->posts()
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => [
                'tag' => $tag,
                'posts' => $posts
            ],
            'message' => 'Tag posts retrieved successfully.'
        ]);
    }

    /**
     * Update the specified tag in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $tag = Tag::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('tags')->ignore($tag->id)
            ],
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tag->update($request->all());

        return response()->json([
            'data' => $tag,
            'message' => 'Tag updated successfully.'
        ]);
    }

    /**
     * Remove the specified tag from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $tag = Tag::findOrFail($id);

        // Detach all posts from this tag
        $tag->posts()->detach();
        
        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully.'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    /**
     * Display a listing of published pages.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $pages = Page::query()
            ->with(['media', 'parent'])
            ->where('status', 'published')
            ->where('published_at', '<=', Carbon::now())
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function ($page) {
                return $this->formatPageResponse($page);
            });

        return response()->json([
            'data' => $pages,
            'message' => 'Pages retrieved successfully.'
        ]);
    }

    /**
     * Display the specified page by slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::with(['media', 'parent', 'author'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where('published_at', '<=', Carbon::now())
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatPageResponse($page, true),
            'message' => 'Page retrieved successfully.'
        ]);
    }

    /**
     * Format the page response with all necessary data for the frontend.
     *
     * @param Page $page
     * @param bool $includeContent
     * @return array
     */
    protected function formatPageResponse(Page $page, bool $includeContent = false): array
    {
        $response = [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'excerpt' => $page->excerpt,
            'template' => $page->template,
            'created_at' => $page->created_at->toIso8601String(),
            'updated_at' => $page->updated_at->toIso8601String(),
            'published_at' => $page->published_at?->toIso8601String(),
            'meta' => $page->meta ?? [],
            'seo' => $this->formatSeoData($page),
            'featured_image' => $this->getFeaturedImageUrl($page),
            'author' => $page->author ? [
                'id' => $page->author->id,
                'name' => $page->author->name,
                'avatar' => $page->author->profile_photo_url ?? null,
            ] : null,
            'parent' => $page->parent ? [
                'id' => $page->parent->id,
                'title' => $page->parent->title,
                'slug' => $page->parent->slug,
            ] : null,
        ];

        if ($includeContent) {
            $response['content'] = $page->content;
        }

        return $response;
    }

    /**
     * Format SEO data for the page.
     *
     * @param Page $page
     * @return array
     */
    protected function formatSeoData(Page $page): array
    {
        $seo = $page->seo ?? [];
        
        return array_merge([
            'title' => $page->meta_title ?? $page->title,
            'description' => $page->meta_description ?? $page->excerpt,
            'keywords' => $page->meta_keywords ?? '',
            'og_title' => $page->meta_title ?? $page->title,
            'og_description' => $page->meta_description ?? $page->excerpt,
            'og_image' => $this->getFeaturedImageUrl($page),
            'og_type' => 'website',
        ], $seo);
    }

    /**
     * Get the featured image URL with different sizes.
     *
     * @param Page $page
     * @return array|null
     */
    protected function getFeaturedImageUrl(Page $page): ?array
    {
        $media = $page->getFirstMedia('featured_image');
        
        if (!$media) {
            return null;
        }

        return [
            'original' => $media->getFullUrl(),
            'large' => $media->getFullUrl('large'),
            'medium' => $media->getFullUrl('medium'),
            'thumbnail' => $media->getFullUrl('thumbnail'),
            'alt' => $media->getCustomProperty('alt', ''),
            'caption' => $media->getCustomProperty('caption', ''),
        ];
    }

    /**
     * Get a page by its slug.
     * This is an alias for the show method for better readability in routes.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getPageBySlug(string $slug): JsonResponse
    {
        return $this->show($slug);
    }


    /**
     * Get pages for the navigation menu.
     *
     * @return JsonResponse
     */
    public function menu(): JsonResponse
    {
        $pages = Page::query()
            ->select(['id', 'title', 'slug', 'parent_id', 'sort_order'])
            ->where('status', 'published')
            ->where('show_in_menu', true)
            ->where('published_at', '<=', Carbon::now())
            ->orderBy('sort_order')
            ->get()
            ->toTree();

        return response()->json([
            'data' => $this->formatMenuItems($pages),
            'message' => 'Menu items retrieved successfully.'
        ]);
    }

    /**
     * Format menu items for the frontend.
     *
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return array
     */
    protected function formatMenuItems($items): array
    {
        return $items->map(function ($item) {
            $formatted = [
                'id' => $item->id,
                'title' => $item->title,
                'url' => '/' . ltrim($item->slug, '/'),
                'active' => request()->is(trim($item->slug, '/') . '*'),
            ];

            if ($item->children->isNotEmpty()) {
                $formatted['children'] = $this->formatMenuItems($item->children);
            }

            return $formatted;
        })->toArray();
    }

    /**
     * Get the homepage content.
     *
     * @return JsonResponse
     */
    public function homepage(): JsonResponse
    {
        $page = Page::query()
            ->with(['media', 'author'])
            ->where('is_homepage', true)
            ->where('status', 'published')
            ->where('published_at', '<=', Carbon::now())
            ->firstOrFail();

        return response()->json([
            'data' => $this->formatPageResponse($page, true),
            'message' => 'Homepage retrieved successfully.'
        ]);
    }
}

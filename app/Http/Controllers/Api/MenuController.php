<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index(): JsonResponse
    {
        $menus = Menu::with(['items' => function ($query) {
            $query->with('children');
        }])->get();

        return response()->json([
            'data' => $menus,
            'message' => 'Menus retrieved successfully'
        ]);
    }
    /**
     * Get menu by location.
     */
    public function byLocation(string $location): JsonResponse
    {
        $menus = Menu::with(['items' => function ($query) {
            $query->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => function ($q) {
                    $q->where('is_active', true)
                        ->orderBy('sort_order');
                }])
                ->orderBy('sort_order');
        }])
            ->where('location', $location)
            ->where('is_active', true)
            ->get();

        if (!$menus->count()) {
            return response()->json([
                'message' => 'Menu not found for the specified location'
            ], 404);
        }

        return response()->json([
            'data' => $menus,
            'message' => 'Menu retrieved successfully'
        ]);
    }

    /**
     * Display the specified menu.
     */
    public function show(Menu $menu): JsonResponse
    {
        return response()->json([
            'data' => $menu->load(['items' => function ($query) {
                $query->with('children');
            }]),
            'message' => 'Menu retrieved successfully'
        ]);
    }
}

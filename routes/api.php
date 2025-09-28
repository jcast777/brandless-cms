<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\ApiTokenController;

// Health check endpoint
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});


// Public routes (require API token)
Route::middleware('api.token')->group(function () {
    Route::prefix('content')->group(function () {
        // Posts
        Route::get('posts', [PostController::class, 'index']);
        Route::get('posts/featured', [PostController::class, 'featured']);
        Route::get('posts/{slug}', [PostController::class, 'show']);
        
        // Pages
        Route::get('pages', [PageController::class, 'index']);
        Route::get('pages/{slug}', [PageController::class, 'show']);
        
        // Categories
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{slug}', [CategoryController::class, 'show']);
        Route::get('categories/{slug}/posts', [CategoryController::class, 'posts']);
        
        // Tags
        Route::get('tags', [TagController::class, 'index']);
        Route::get('tags/{slug}', [TagController::class, 'show']);
        Route::get('tags/{slug}/posts', [TagController::class, 'posts']);
    });
    
    // Menus (now require API token)
    Route::prefix('menus')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::get('{location}', [MenuController::class, 'byLocation']);
    });
    
    // Media (now require API token)
    Route::prefix('media')->group(function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::get('{id}', [MediaController::class, 'show']);
    });
    
    // Settings (public only, now require API token)
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'public']);
        Route::get('theme', [SettingController::class, 'theme']);
        Route::get('{group}', [SettingController::class, 'group']);
    });
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // API Token management
    Route::prefix('tokens')->group(function () {
        Route::get('/', [ApiTokenController::class, 'index']);
        Route::post('/', [ApiTokenController::class, 'store']);
        Route::get('{apiToken}', [ApiTokenController::class, 'show']);
        Route::put('{apiToken}', [ApiTokenController::class, 'update']);
        Route::delete('{apiToken}', [ApiTokenController::class, 'destroy']);
    });

    // User content management
    Route::apiResource('posts', PostController::class)->except(['index', 'show'])->names([
        'store' => 'my.posts.store',
        'update' => 'my.posts.update',
        'destroy' => 'my.posts.destroy'
    ]);
    Route::apiResource('pages', PageController::class)->except(['index', 'show'])->names([
        'store' => 'my.pages.store',
        'update' => 'my.pages.update',
        'destroy' => 'my.pages.destroy'
    ]);

    // Admin routes (require admin role)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Full CRUD for all resources
        Route::apiResource('posts', PostController::class)->names([
            'index' => 'admin.posts.index',
            'store' => 'admin.posts.store',
            'show' => 'admin.posts.show',
            'update' => 'admin.posts.update',
            'destroy' => 'admin.posts.destroy'
        ]);
        Route::apiResource('pages', PageController::class)->names([
            'index' => 'admin.pages.index',
            'store' => 'admin.pages.store',
            'show' => 'admin.pages.show',
            'update' => 'admin.pages.update',
            'destroy' => 'admin.pages.destroy'
        ]);
        Route::apiResource('categories', CategoryController::class)->names([
            'index' => 'admin.categories.index',
            'store' => 'admin.categories.store',
            'show' => 'admin.categories.show',
            'update' => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy'
        ]);
        Route::apiResource('tags', TagController::class)->names([
            'index' => 'admin.tags.index',
            'store' => 'admin.tags.store',
            'show' => 'admin.tags.show',
            'update' => 'admin.tags.update',
            'destroy' => 'admin.tags.destroy'
        ]);
        Route::apiResource('menus', MenuController::class)->names([
            'index' => 'admin.menus.index',
            'store' => 'admin.menus.store',
            'show' => 'admin.menus.show',
            'update' => 'admin.menus.update',
            'destroy' => 'admin.menus.destroy'
        ]);
        
        // Settings management
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index']);
            Route::post('/', [SettingController::class, 'store']);
            Route::put('{id}', [SettingController::class, 'update']);
            Route::delete('{id}', [SettingController::class, 'destroy']);
        });

        // Media management
        Route::prefix('media')->group(function () {
            Route::post('upload', [MediaController::class, 'upload']);
            Route::delete('{id}', [MediaController::class, 'destroy']);
        });

        // API Token management (admin)
        Route::prefix('tokens')->group(function () {
            Route::get('all', [ApiTokenController::class, 'adminIndex']);
            Route::post('public', [ApiTokenController::class, 'createPublicToken']);
        });
    });
});

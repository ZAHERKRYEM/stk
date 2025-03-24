<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BannerController;

Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':c-category'])->post('/categories', [CategoryController::class, 'store']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':r-category'])->get('/categories', [CategoryController::class, 'index']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':u-category'])->put('/categories/{id}', [CategoryController::class, 'update']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':d-category'])->delete('/categories/{id}', [CategoryController::class, 'destroy']);

Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':c-product'])->post('/products', [ProductController::class, 'store']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':r-product'])->get('/products', [ProductController::class, 'index']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':u-product'])->put('/products/{id}', [ProductController::class, 'update']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class . ':d-product'])->delete('/products/{id}', [ProductController::class, 'destroy']);

Route::middleware(['auth:sanctum', PermissionMiddleware::class .':c-banner'])->post('/banners', [BannerController::class, 'store']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class .':r-banner'])->get('/banners', [BannerController::class, 'index']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class .':u-banner'])->put('/banners/{id}', [BannerController::class, 'update']);
Route::middleware(['auth:sanctum', PermissionMiddleware::class .':d-banner'])->delete('/banners/{id}', [BannerController::class, 'destroy']);



Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum',SuperAdminMiddleware::class])->post('/register', [AuthController::class, 'register']);
// Route::post('/register', [AuthController::class, 'register']);
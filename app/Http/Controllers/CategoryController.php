<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $language = $request->query('language', 'en');
            $categories = Category::where('name_translations', 'like', '%"' . $language . '":%')->get();
            return $this->successResponse('Categories retrieved successfully', $categories);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function store(Request $request)
    {
        try {
            //  Validate request data
            $validator = Validator::make($request->all(), [
                'name_translations' => 'required|array',
                'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
    
            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }
    
            //  Check if the category name (in English) already exists
            $nameEn = $request->name_translations['en'] ?? null;
            if ($nameEn && Category::where('name_translations->en', $nameEn)->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category name already exists',
                    'data' => null,
                    'status_code' => 422,
                ], 422);
            }
    
            //  Create category without the image first
            $category = Category::create([
                'name_translations' => $request->name_translations,
            ]);
    
            if ($request->hasFile('image_url')) {
                //  Add the image to the 'categories' collection
                $category->addMedia($request->file('image_url'))
                    ->toMediaCollection('categories');
    
                // Refresh the model to update data after saving the image
                $category->refresh();
    
                //  Update 'image_url' with the converted WebP image URL
                $category->update([
                    'image_url' => $category->getFirstMediaUrl('categories', 'webp'),
                ]);
            }
    
            return $this->successResponse('Category created successfully', $category, 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }
    
    
    
    public function show(Category $category)
    {
        $category->image_url = Storage::url($category->image_url);
        return $this->successResponse('Category retrieved successfully', $category);
    }

    public function update(Request $request, Category $category)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name_translations' => 'required|array',
                'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            if ($request->hasFile('image_url')) {
                if ($category->image_url) {
                    Storage::disk('public')->delete($category->image_url);
                }
                $imagePath = $request->file('image_url')->store('categories', 'public');
            } else {
                $imagePath = $category->image_url;
            }

            $category->update([
                'name_translations' => $request->name_translations,
                'image_url' => $imagePath,
            ]);

            return $this->successResponse('Category updated successfully', $category);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->image_url) {
                Storage::disk('public')->delete($category->image_url);
            }
            $category->delete();
            return $this->successResponse('Category deleted successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    private function successResponse(string $message, $data = null, int $statusCode = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'status_code' => $statusCode,
        ], $statusCode);
    }

    private function validationErrorResponse($validator)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors(),
            'status_code' => 422,
        ], 422);
    }

    private function errorResponse(\Throwable $e, int $statusCode = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'data' => null,
            'status_code' => $statusCode,
        ], $statusCode);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name_translations' => 'required|array',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $imagePath = $request->hasFile('image') ? 
                $request->file('image')->store('categories', 'public') : null;

            $category = Category::create([
                'name_translations' => json_encode($request->name_translations, JSON_UNESCAPED_UNICODE),
                'image_url' => $imagePath ? url('storage/' . $imagePath) : null,
            ]);

            return $this->successResponse('تم إنشاء الفئة بنجاح', $this->formatCategory($category), 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function index()
    {
        try {
            $categories = Category::all()->map(function ($category) {
                return $this->formatCategory($category);
            });

            return $this->successResponse('تم جلب الفئات بنجاح', $categories);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->errorResponse('لم يتم العثور على الفئة', 404);
            }

            return $this->successResponse('تم جلب الفئة بنجاح', $this->formatCategory($category));
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->errorResponse('لم يتم العثور على الفئة', 404);
            }

            $validator = Validator::make($request->all(), [
                'name_translations' => 'sometimes|required|array',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            if ($request->has('name_translations')) {
                $category->name_translations = json_encode($request->name_translations, JSON_UNESCAPED_UNICODE);
            }

            if ($request->hasFile('image')) {
                if ($category->image_url) {
                    Storage::disk('public')->delete(str_replace(url('storage/') . '/', '', $category->image_url));
                }
                $imagePath = $request->file('image')->store('categories', 'public');
                $category->image_url = url('storage/' . $imagePath);
            }

            $category->save();

            return $this->successResponse('تم تحديث الفئة بنجاح', $this->formatCategory($category));
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->errorResponse('لم يتم العثور على الفئة', 404);
            }

            if ($category->image_url) {
                Storage::disk('public')->delete(str_replace(url('storage/') . '/', '', $category->image_url));
            }

            $category->delete();

            return $this->successResponse('تم حذف الفئة بنجاح');
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    private function formatCategory($category)
    {
        return [
            'id' => $category->id,
            'name_translations' => json_decode($category->name_translations, true),
            'image_url' => $category->image_url,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
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
            'message' => 'أخطاء في التحقق من البيانات',
            'data' => $validator->errors(),
            'status_code' => 422,
        ], 422);
    }

    private function errorResponse($error, int $statusCode = 500)
    {
        return response()->json([
            'status' => false,
            'message' => is_string($error) ? $error : $error->getMessage(),
            'data' => null,
            'status_code' => $statusCode,
        ], $statusCode);
    }
}

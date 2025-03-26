<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use Illuminate\Support\Str;

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

            $image = $request->file('image');
            $imageName = Str::random(40).'.'.$image->getClientOriginalExtension();
            $image->move(public_path('storage/categories'), $imageName);


            $category = Category::create([
                'name_translations' => json_encode($request->name_translations, JSON_UNESCAPED_UNICODE),
                'image_url' => 'storage/categories/' . $imageName,
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
                    $oldImagePath = public_path('storage/categories/' . basename($category->image_url));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $image = $request->file('image');
                $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('storage/categories'), $imageName);
                $category->image_url = 'storage/categories/' . $imageName;
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
                $imagePath = public_path('storage/categories/' . basename($category->image_url));
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
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
            'image_url' => url($category->image_url),
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

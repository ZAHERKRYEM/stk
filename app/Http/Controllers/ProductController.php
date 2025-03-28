<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();
            return $this->successResponse('Products retrieved successfully', $products);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_code' => 'required|string|unique:products',
                'name_translations' => 'required|array',
                'description_translations' => 'required|array',
                'image' => 'nullable|image',
                'category_id' => 'required|exists:categories,id',
                'country_of_origin' => 'required|string',
                'material_property' => 'nullable|string',
                'product_category' => 'nullable|string',
                'weight_unit' => 'nullable|string',
                'barcode' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            // إنشاء المنتج
            $product = Product::create($request->only([
                'product_code',
                'name_translations',
                'image',
                'description_translations',
                'category_id',
                'country_of_origin',
                'material_property',
                'product_category',
                'weight_unit',
                'barcode'
            ]));

            // إضافة الصورة الرئيسية
            if ($request->hasFile('image')) {
                $product->addMedia($request->file('image'))
                    ->toMediaCollection('images');
                $product->update([
                    'image_url' => $product->getFirstMediaUrl('images', 'webp'),
                ]);
            }

            // إضافة المتغيرات الخاصة بالمنتج (variants) عبر كونترولر الـ Variant
            if ($request->has('variants')) {
                app(ProductVariantController::class)->store($request->variants, $product);
            }

            return $this->successResponse('Product created successfully', $product, 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function show(Product $product)
    {
        return $this->successResponse('Product retrieved successfully', $product);
    }

    public function update(Request $request, Product $product)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_code' => 'required|string|unique:products,product_code,' . $product->id,
                'name_translations' => 'required|array',
                'image' => 'nullable|image',
                'description_translations' => 'required|array',
                'category_id' => 'required|exists:categories,id',
                'country_of_origin' => 'required|string',
                'material_property' => 'nullable|string',
                'product_category' => 'nullable|string',
                'weight_unit' => 'nullable|string',
                'barcode' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            // تحديث المنتج
            $product->update($request->all());

            // تحديث الصورة الرئيسية
            if ($request->hasFile('image')) {
                $product->clearMediaCollection('images');
                $product->addMedia($request->file('image'))->toMediaCollection('images');
            }

            // تحديث المتغيرات الخاصة بالمنتج عبر كونترولر الـ Variant
            if ($request->has('variants')) {
                app(ProductVariantController::class)->update($request->variants, $product,$product->id);
            }

            return $this->successResponse('Product updated successfully', $product);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return $this->successResponse('Product deleted successfully');
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

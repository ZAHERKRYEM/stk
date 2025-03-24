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
                'description_translations' => 'nullable|array',
                'price' => 'required|numeric',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image', 
                'gallery' => 'nullable|array',  
                'sizes' => 'nullable|array',
                'country_of_origin' => 'nullable|string',
                'material_property' => 'nullable|string',
                'product_category' => 'nullable|string',
                'gross_weight' => 'nullable|numeric',
                'net_weight' => 'nullable|numeric',
                'tare_weight' => 'nullable|numeric',
                'standard_weight' => 'nullable|numeric',
                'free_quantity' => 'nullable|integer',
                'weight_unit' => 'nullable|string',
                'packaging' => 'nullable|string',
                'supplier_name' => 'nullable|string',
                'box_gross_weight' => 'nullable|numeric',
                'box_dimensions' => 'nullable|string',
                'box_packing' => 'nullable|string',
                'in_stock' => 'nullable|boolean',
                'is_hidden' => 'nullable|boolean',
                'is_new' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

          
            $product = Product::create($request->all());

       
            if ($request->hasFile('image')) {
                $product->addMedia($request->file('image'))->toMediaCollection('images');
            }

        
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $product->addMedia($image)->toMediaCollection('gallery');
                }
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
                'description_translations' => 'nullable|array',
                'price' => 'required|numeric',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image',
                'gallery' => 'nullable|array',
                'sizes' => 'nullable|array',
                'country_of_origin' => 'nullable|string',
                'material_property' => 'nullable|string',
                'product_category' => 'nullable|string',
                'gross_weight' => 'nullable|numeric',
                'net_weight' => 'nullable|numeric',
                'tare_weight' => 'nullable|numeric',
                'standard_weight' => 'nullable|numeric',
                'free_quantity' => 'nullable|integer',
                'weight_unit' => 'nullable|string',
                'packaging' => 'nullable|string',
                'supplier_name' => 'nullable|string',
                'box_gross_weight' => 'nullable|numeric',
                'box_dimensions' => 'nullable|string',
                'box_packing' => 'nullable|string',
                'in_stock' => 'nullable|boolean',
                'is_hidden' => 'nullable|boolean',
                'is_new' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

   
            $product->update($request->all());

  
            if ($request->hasFile('image')) {
                $product->clearMediaCollection('images');  
                $product->addMedia($request->file('image'))->toMediaCollection('images');
            }

   
            if ($request->hasFile('gallery')) {
                $product->clearMediaCollection('gallery'); 
                foreach ($request->file('gallery') as $image) {
                    $product->addMedia($image)->toMediaCollection('gallery');
                }
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

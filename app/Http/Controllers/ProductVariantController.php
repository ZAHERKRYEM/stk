<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the variants for a specific product.
     */
    public function index()
    {
        try {
            // Eager load the variants relationship with the products
            $products = Product::with('variants')->get();
    
            // Return a success response with the products and their variants
            return $this->successResponse('Products retrieved successfully', $products);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Store a newly created variant for a specific product.
     */
    public function store(Request $request)
    {
        try {
            // Validate incoming request data
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
    
            // Create a new product record
            $product = Product::create($request->only([
                'product_code',
                'name_translations',
                'description_translations',
                'category_id',
                'country_of_origin',
                'material_property',
                'product_category',
                'weight_unit',
                'barcode'
            ]));
    
            // If an image is uploaded, add it to the media collection
            if ($request->hasFile('image')) {
                $product->addMedia($request->file('image'))
                    ->toMediaCollection('variant_image'); // Save image in 'variant_image' collection
                // Update product with the original image URL
                $product->update([
                    'image_url' => $product->getFirstMediaUrl('variant_image'), // Get URL for the original image
                ]);
            }
    
            // Return a success response with the created product
            return $this->successResponse('Product created successfully', $product, 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }
    
    public function update(Request $request, Product $product)
    {
        try {
            // Validate the incoming update request
            $validator = Validator::make($request->all(), [
                'product_code' => 'required|string|unique:products,product_code,' . $product->id,
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
    
            // Update product data
            $product->update($request->all());
    
            // If a new image is uploaded, update the product's image
            if ($request->hasFile('image')) {
                // Clear the old image from the media collection
                $product->clearMediaCollection('variant_image');
                // Add the new image to the media collection
                $product->addMedia($request->file('image'))->toMediaCollection('variant_image');
            }
    
            // Return a success response with the updated product
            return $this->successResponse('Product updated successfully', $product);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }
    

    /**
     * Display a specific variant of a product.
     */
    public function show(Product $product, ProductVariant $variant)
    {
        return response()->json([
            'success' => true,
            'message' => 'Variant retrieved successfully',
            'data' => $variant
        ]);
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        try {
            // Delete the media file
            $media = $variant->getFirstMedia('variant_image');
            if ($media) {
                $variant->deleteMedia($media->id);
            }

            // Delete the variant record
            $variant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Handle validation error response.
     */
    private function validationErrorResponse($validator)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    /**
     * Handle general error response.
     */
    private function errorResponse(\Throwable $e, int $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], $statusCode);
    }
}

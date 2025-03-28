<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the product variants for a specific product.
     */
    public function index($product_id)
    {
        $variants = ProductVariant::where('product_id', $product_id)->with('product')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $variants
        ]);
    }

    /**
     * Store a new product variant for a specific product.
     */
    public function store(Request $request, $product_id)
    {
        // Ensure the product exists
        $product = Product::findOrFail($product_id);

        $validator = Validator::make($request->all(), [
            'size'            => ['nullable', 'string', 'max:255'],
            'price'           => ['required', 'numeric', 'min:0'],
            'gross_weight'    => ['nullable', 'numeric', 'min:0'],
            'net_weight'      => ['nullable', 'numeric', 'min:0'],
            'tare_weight'     => ['nullable', 'numeric', 'min:0'],
            'standard_weight' => ['nullable', 'numeric', 'min:0'],
            'free_quantity'   => ['nullable', 'integer', 'min:0'],
            'packaging'       => ['nullable', 'string', 'max:255'],
            'box_dimensions'  => ['nullable', 'string', 'max:255'],
            'box_packing'     => ['nullable', 'string', 'max:255'],
            'in_stock'        => ['required', 'boolean'],
            'is_hidden'       => ['required', 'boolean'],
            'is_new'          => ['required', 'boolean'],
            'variant_image'   => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $variant = $product->variants()->create($validator->validated());

        // Handle image upload
        if ($request->hasFile('variant_image')) {
            $variant
                ->addMediaFromRequest('variant_image')
                ->toMediaCollection('variant_image');
        }

        return response()->json([
            'success' => true,
            'data' => $variant->load('product')
        ], 201);
    }

    /**
     * Display the specified product variant for a specific product.
     */
    public function show($product_id, $id)
    {
        $variant = ProductVariant::where('product_id', $product_id)->with('product')->find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $variant
        ]);
    }

    /**
     * Update the specified product variant for a specific product.
     */
    public function update(Request $request, $product_id, $id)
    {
        $variant = ProductVariant::where('product_id', $product_id)->find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'size'            => ['nullable', 'string', 'max:255'],
            'price'           => ['sometimes', 'numeric', 'min:0'],
            'gross_weight'    => ['nullable', 'numeric', 'min:0'],
            'net_weight'      => ['nullable', 'numeric', 'min:0'],
            'tare_weight'     => ['nullable', 'numeric', 'min:0'],
            'standard_weight' => ['nullable', 'numeric', 'min:0'],
            'free_quantity'   => ['nullable', 'integer', 'min:0'],
            'packaging'       => ['nullable', 'string', 'max:255'],
            'box_dimensions'  => ['nullable', 'string', 'max:255'],
            'box_packing'     => ['nullable', 'string', 'max:255'],
            'in_stock'        => ['sometimes', 'boolean'],
            'is_hidden'       => ['sometimes', 'boolean'],
            'is_new'          => ['sometimes', 'boolean'],
            'variant_image'   => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $variant->update($validator->validated());

        // Handle image update
        if ($request->hasFile('variant_image')) {
            $variant->clearMediaCollection('variant_image');
            $variant
                ->addMediaFromRequest('variant_image')
                ->toMediaCollection('variant_image');
        }

        return response()->json([
            'success' => true,
            'data' => $variant->load('product')
        ]);
    }

    /**
     * Remove the specified product variant.
     */
    public function destroy($product_id, $id)
    {
        $variant = ProductVariant::where('product_id', $product_id)->find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Product variant not found.'
            ], 404);
        }

        $variant->clearMediaCollection('variant_image');
        $variant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product variant deleted successfully.'
        ]);
    }
}

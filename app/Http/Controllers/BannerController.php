<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    //  Retrieve all active banners
    public function index()
    {
        try {
            $banners = Banner::where('is_active', true)->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'is_active' => $banner->is_active,
                    'image_url' => $banner->getFirstMediaUrl('banners'),
                ];
            });
    
            return $this->successResponse('Banners retrieved successfully', $banners);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }
    

    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $banner = Banner::create([
            'is_active' => $request->is_active,
        ]);

        if ($request->hasFile('image')) {
            $banner->addMediaFromRequest('image')
                ->toMediaCollection('banners', 'public');

            // تحديث الرابط بعد الرفع
            $banner->refresh();
        }

        return $this->successResponse('Banner created successfully', [
            'id' => $banner->id,
            'is_active' => $banner->is_active,
            'image_url' => $banner->getFirstMediaUrl('banners'),
        ], 201);
    } catch (\Throwable $e) {
        return $this->errorResponse($e);
    }
}

public function update(Request $request, Banner $banner)
{
    try {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $banner->update([
            'is_active' => $request->is_active,
        ]);

        if ($request->hasFile('image')) {
            $banner->clearMediaCollection('banners');
            $banner->addMediaFromRequest('image')
                ->toMediaCollection('banners', 'public');
        }

        return $this->successResponse('Banner updated successfully', [
            'id' => $banner->id,
            'is_active' => $banner->is_active,
            'image_url' => $banner->getFirstMediaUrl('banners'),
        ]);
    } catch (\Throwable $e) {
        return $this->errorResponse($e);
    }
}

    //  Delete a banner
    public function destroy(Banner $banner)
    {
        try {
            $banner->clearMediaCollection('banners');
            $banner->delete();

            return $this->successResponse('Banner deleted successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    //  Function to return a successful response
    private function successResponse(string $message, $data = null, int $statusCode = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'status_code' => $statusCode,
        ], $statusCode);
    }

    //  Function to return validation errors
    private function validationErrorResponse($validator)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors(),
            'status_code' => 422,
        ], 422);
    }

    //  Function to return an error response
    private function errorResponse(\Throwable $e, int $statusCode = 500)
    {
        logger()->error($e->getMessage());

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'data' => null,
            'status_code' => $statusCode,
        ], $statusCode);
    }
}

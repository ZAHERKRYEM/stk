<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Banner;

class BannerController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $image_url = $request->file('image')->store('banners', 'public');

            $banner = Banner::create([
                'is_active' => $request->input('is_active', true),
                'image_url' => $image_url,
            ]);

            return $this->successResponse('تم رفع الصورة بنجاح', $this->formatBanner($banner), 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function index()
    {
        try {
            $banners = Banner::all()->map(function ($banner) {
                return $this->formatBanner($banner);
            });

            return $this->successResponse('تم جلب الصور بنجاح', $banners);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function show($id)
    {
        try {
            $banner = Banner::find($id);
            if (!$banner) {
                return $this->errorResponse('لم يتم العثور على الصورة', 404);
            }

            return $this->successResponse('تم جلب الصورة بنجاح', $this->formatBanner($banner));
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $banner = Banner::find($id);
            if (!$banner) {
                return $this->errorResponse('لم يتم العثور على الصورة', 404);
            }

            $validator = Validator::make($request->all(), [
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($banner->image_path);
                $imagePath = $request->file('image')->store('banners', 'public');
                $banner->image_url = $imagePath;
            }

            if ($request->has('is_active')) {
                $banner->is_active = $request->input('is_active');
            }

            $banner->save();

            return $this->successResponse('تم تحديث الصورة بنجاح', $this->formatBanner($banner));
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function destroy($id)
    {
        try {
            $banner = Banner::find($id);
            if (!$banner) {
                return $this->errorResponse('لم يتم العثور على الصورة', 404);
            }

            Storage::disk('public')->delete($banner->image_path);
            $banner->delete();

            return $this->successResponse('تم حذف الصورة بنجاح');
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    private function formatBanner($banner)
    {
        return [
            'id' => $banner->id,
            'is_active' => $banner->is_active,
            'image_url' => url('storage/' . $banner->image_url),
            'created_at' => $banner->created_at,
            'updated_at' => $banner->updated_at,
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

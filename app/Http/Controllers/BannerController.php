<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\Banner;
use Illuminate\Support\Str;
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

            $image = $request->file('image');
            $imageName = Str::random(40).'.'.$image->getClientOriginalExtension();
            $image->move(public_path('storage/banners'), $imageName);

            $banner = Banner::create([
                'is_active' => $request->input('is_active', true),
                'image_url' => 'storage/banners/' . $imageName,
            ]);

            return $this->successResponse('تم رفع الصورة بنجاح', $this->formatBanner($banner), 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function index()
    {
        try {
            $banners = Banner::all()->map(fn($banner) => $this->formatBanner($banner));
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
                if (file_exists(public_path($banner->image_url))) {
                    unlink(public_path($banner->image_url));
                }
                $image = $request->file('image');
                $imageName = Str::random(40).'.'.$image->getClientOriginalExtension();
                $image->move(public_path('storage/banners'), $imageName);
                $banner->image_url = 'storage/banners/' . $imageName;
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

            if (file_exists(public_path($banner->image_url))) {
                unlink(public_path($banner->image_url));
            }
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
            'image_url' => url($banner->image_url),
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

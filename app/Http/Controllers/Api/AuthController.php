<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|unique:users',
                'company_name' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'port' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|string|exists:roles,name',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|exists:permissions,name'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company_name' => $request->company_name,
                'country' => $request->country,
                'address' => $request->address,
                'port' => $request->port,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);
            if ($request->has('permissions')) {
                $user->givePermissionTo($request->permissions);
            }

            return $this->successResponse('تم تسجيل المستخدم بنجاح!', [
                'user' => $user->only(['id', 'name', 'email', 'phone', 'company_name', 'country', 'address', 'port']),
                'role' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->validationErrorResponse(['email' => ['المعلومات غير صحيحة.']]);
            }

            return $this->successResponse('تم تسجيل الدخول بنجاح', [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return $this->successResponse('تم تسجيل الخروج بنجاح');
    }

    public function registerAgent(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
                'company_name' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'port' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'company_name' => $request->company_name,
                'country' => $request->country,
                'address' => $request->address,
                'port' => $request->port,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('agent');

            return $this->successResponse('تم تسجيل الوكيل بنجاح!', [
                'user' => $user->only(['id', 'name', 'email', 'phone', 'company_name', 'country', 'address', 'port']),
                'role' => $user->getRoleNames(),
            ]);
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

    private function validationErrorResponse($errors, int $statusCode = 422)
    {
        return response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $errors,
            'status_code' => $statusCode,
        ], $statusCode);
    }

    private function errorResponse(\Throwable $e, int $statusCode = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
            'status_code' => $statusCode,
        ], $statusCode);
    }
}

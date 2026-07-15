<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:12',
            'company_name' => 'required|string|max:255',
            'company_subdomain' => 'required|string|alpha_dash|max:255|unique:companies,subdomain',
        ]);

        // Create company
        $company = Company::create([
            'name' => $validated['company_name'],
            'subdomain' => $validated['company_subdomain'],
            'is_active' => true,
            'created_by' => 1, // temporary
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $company->id,
            'is_master_admin' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('company_admin');
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = User::where('email', $validated['email'])->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user()->load('company'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent'])
            : response()->json(['message' => 'Unable to send reset link'], 400);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:12|confirmed',
            'token' => 'required|string',
        ]);

        $status = Password::reset($validated, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset'])
            : response()->json(['message' => 'Invalid token'], 400);
    }
}

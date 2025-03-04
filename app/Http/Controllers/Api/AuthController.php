<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',


        ]);
        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('users', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'image' => $imagePath,
        ]);
        $token = $user->createToken('LaravelAuthApp')->plainTextToken;
        $user->sendEmailVerificationNotification();
        $verifyUrl = URL::signedRoute('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
        return response()->json([
            'success' => true,
            'message' => __('messages.register'),
            'user' => $user,
            'token' => $token,
            'verify_url' => $verifyUrl,
        ], 201);
    }
    public function login(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('LaravelAuthApp')->plainTextToken;

        return response()->json(['message' =>__('messages.login'), 'token' => $token], 200);
    }
    public function user()
    {
        return response()->json(['message'=>__('messages.user'),Auth::user(), 200]);
    }
    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => __('messages.logout')], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $admin = Admin::where('email', $request->email)->first();
        if ($admin) {
            return response()->json([
                'message' => 'Email already exists'
            ]);
        }

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if (!$admin) {
            return response()->json([
                'message' => "User registered failed",
            ]);
        }
        $secretKey = env('SECRET_KEY');
        $payload = [
            'id' => $admin->id,
            'name' => $admin->name,
            'role' => 'admin',
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $jwt = JWT::encode($payload, $secretKey, 'HS256');
        return response()->json([
            'message' => 'User registered successfully',
            'admin' => $admin,
            'token' => $jwt,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $admin = Admin::where('email', $request->email)->first();
        if (!$admin) {
            return response()->json([
                'message' => 'Email does not exist'
            ]);
        }
        if (!Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Password is incorrect',
            ]);
        }
        $secretKey = env('SECRET_KEY');
        $payload = [
            'id' => $admin->id,
            'name' => $admin->name,
            'role' => 'admin',
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        $jwt = JWT::encode($payload, $secretKey, 'HS256');
        return response()->json([
            'message' => 'User logged in successfully',
            'admin' => $admin,
            'token' => $jwt,
        ]);
    }
}

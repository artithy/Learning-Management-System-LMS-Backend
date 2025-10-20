<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'phone' => 'required|string',
            'address' => 'required|string',
            'dob' => 'nullable|date',
        ]);

        $student = Student::where('email', $request->email)->first();
        if ($student) {
            return response()->json([
                'message' => 'Email already exists'
            ]);
        }

        $student = Student::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'dob' => $request->dob,
            'gender' => $request->gender,
        ]);

        $secretKey = env('SECRET_KEY');
        $payload = [
            'id' => $student->id,
            'name' => $student->name,
            'role' => 'student',
            'email' => $student->email,
            'phone' => $student->phone,
            'address' => $student->address,
            'dob' => $student->dob,
            'gender' => $student->gender,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        return response()->json([
            'message' => 'User registered successfully',
            'student' => $student,
            'tokeb' => $jwt,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $student = Student::where('email', $request->email)->first();
        if (!$student) {
            return response()->json([
                'message' => 'Email does not exist',
            ]);
        }

        if (!Hash::check($request->password, $student->password)) {
            return response()->json([
                'message' => 'password is incorrect',
            ]);
        }

        $secretKey = env('SECRET_KEY');
        $payload = [
            'id' => $student->id,
            'name' => $student->name,
            'role' => 'student',
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        return response()->json([
            'message' => 'Login Successfull',
            'student' => $student,
            'token' => $jwt,
        ]);
    }
}

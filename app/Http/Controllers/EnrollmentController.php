<?php

namespace App\Http\Controllers;

use App\Models\enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{

    public function enroll(Request $request)
    {
        $student_id = $request->attributes->get('auth_user')['id'];
        $request->validate([
            'course_id' => 'required|integer',
            'payment_method' => 'required|string',
            'payment_transaction_id' => 'required|string',
        ]);

        $existing = enrollment::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Student already enrolled in this course'], 400);
        }

        $enrollment = enrollment::create([
            'student_id' => $student_id,
            'course_id' => $request->course_id,
            'payment_status' => 'completed',
            'payment_method' => $request->payment_method,
            'payment_transaction_id' => $request->payment_transaction_id
        ]);

        return response()->json([
            'message' => 'enrollment successful',
            'enrollment' => $enrollment
        ]);
    }

    public function allEnrollments()
    {
        $enrollments = enrollment::with(['student', 'course'])->get();
        return response()->json([
            'enrollments' => $enrollments
        ]);
    }

    public function studentEnrollments(Request $request)
    {
        $student_id = $request->attributes->get('auth_user')['id'];
        $enrollments = enrollment::with('course')->where('student_id', $student_id)->get();
        return response()->json([
            'enrollments' => $enrollments
        ]);
    }
}

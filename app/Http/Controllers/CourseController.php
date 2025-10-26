<?php

namespace App\Http\Controllers;

use App\Models\CourseModel;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function createCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'discount_price' => 'nullable|numeric',
            'duration' => 'nullable|string',
            'image' => 'nullable|string',
            'category_id' => 'required|integer',
            'instructor_name' => 'nullable|string',
            'total_lessons' => 'nullable|integer',
        ]);

        $imagePath = null;

        if (!empty($request->image)) {
            if (!file_exists(public_path('images'))) {
                mkdir(public_path('images'), 0777, true);
            }

            $base64Image = $request->image;
            $imageInfo = explode(',', $base64Image);
            $extension = str_replace(['data:image/', ';base64'], '', $imageInfo[0]);
            $imageName = 'images/' . uniqid() . '.' . $extension;

            file_put_contents(public_path($imageName), base64_decode($imageInfo[1]));

            $imagePath = $imageName;
        }

        $course = CourseModel::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'duration' => $request->duration,
            'image' => $imagePath,
            'category_id' => $request->category_id,
            'instructor_name' => $request->instructor_name,
            'total_lessons' => $request->total_lessons,
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course,
        ]);
    }

    public function getAllCourses()
    {
        $courses = CourseModel::with('category')->get();
        return response()->json($courses);
    }

    public function getCourseById($id)
    {
        $course = CourseModel::with('category')->find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ]);
        }
        return response()->json($course);
    }

    public function updateCourse(Request $request, $id)
    {
        $course = CourseModel::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ]);
        }

        $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric',
            'discount_price' => 'sometimes|nullable|numeric',
            'duration' => 'sometimes|nullable|string',
            'image' => 'sometimes|nullable|string',
            'category_id' => 'sometimes|required|integer',
            'instructor_name' => 'sometimes|nullable|string',
            'total_lessons' => 'sometimes|nullable|integer',
        ]);

        $course->update($request->all());

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course,
        ]);
    }

    public function deleteCourse($id)
    {
        $course = CourseModel::find($id);
        if (!$course) {
            return response()->json([
                'message' => 'Course not found'
            ]);
        }

        $course->delete();
        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }

    public function getAllCoursesPublic()
    {
        $courses = CourseModel::with('category')->get();
        return response()->json($courses);
    }

    public function getCoursesByCategoryPublic($id)
    {
        $courses = CourseModel::with('category')->where('category_id', $id)->get();
        return response()->json($courses);
    }
}

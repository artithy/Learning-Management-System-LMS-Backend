<?php

namespace App\Http\Controllers;

use App\Models\CourseCategory;
use Illuminate\Http\Request;

class CourseCategoryController extends Controller
{
    public function getAllCategory()
    {
        return response()->json(
            CourseCategory::all()
        );
    }

    public function getCategoryById($id)
    {
        $category = CourseCategory::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ]);
        }

        return response()->json($category);
    }

    public function createCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $category = CourseCategory::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ]);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = CourseCategory::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'category not found'
            ]);
        }

        $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
        $category->update($request->only(['name', 'description']));
        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }

    public function deleteCategory($id)
    {
        $category = CourseCategory::find($id);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ]);
        }
        $category->delete();
        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}

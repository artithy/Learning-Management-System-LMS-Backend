<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseModel extends Model
{
    use HasFactory;
    protected $table = 'courses';

    protected $fillable = [
        'title',
        'description',
        'price',
        'discount_price',
        'duration',
        'image',
        'category_id',
        'status',
        'instructor_name',
        'total_lessons'
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }
}

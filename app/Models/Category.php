<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Only subcategories that are active on menu
    public function rSubCategory()
    {
        return $this->hasMany(SubCategory::class, 'category_id')
                    ->where('show_on_menu', 'Show')
                    ->orderBy('sub_category_order', 'asc');
    }

    // Subcategories' posts should only include published posts
    public function rPost()
    {
        return $this->hasManyThrough(
            Post::class,
            SubCategory::class,
            'category_id',     // Foreign key on SubCategory table
            'sub_category_id', // Foreign key on Post table
            'id',              // Local key on Category table
            'id'               // Local key on SubCategory table
        )->where('status', 'published')
         ->orderBy('id', 'desc');
    }

    public function rLanguage()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}

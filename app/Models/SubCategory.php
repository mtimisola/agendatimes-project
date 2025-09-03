<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    public function rCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * rPost: return only published posts by default (desc order).
     * This ensures homepage/category listing only sees published posts.
     */
    public function rPost()
    {
        return $this->hasMany(Post::class, 'sub_category_id')
                    ->where('status', 'published')
                    ->orderBy('id', 'desc');
    }

    public function rLanguage()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}

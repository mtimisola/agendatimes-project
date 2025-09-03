<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * Fillable fields (matches your current database columns)
     */
    protected $fillable = [
        'post_title',
        'post_detail',
        'post_photo',
        'sub_category_id',
        'language_id',
        'author_id',
        'admin_id',
        'status',   // 'published', 'pending', 'draft'
        'is_share',
        'is_comment',
        'tags',
        'subscriber_send_option',
        'headline_section',
        'visitors',
    ];

    /**
     * Relationships
     */
    public function rSubCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function rLanguage()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function rAuthor()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function rAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Scopes for filtering posts by status
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}

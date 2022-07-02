<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\QueryControlModel;

class Post extends Model
{
    use HasFactory;
    use QueryControlModel;

    /**
     * Relations
     */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Local Scopes
     */

    public function scopeQueryFilter($query)
    {
        return $query
        ->when(request()->query('user'), function ($query, $csv) {
            return $query->whereIn('user_id', explode(','), $csv);
        })
        ->when(request()->query('title'), function ($query, $csv) {
            return $query->whereIn('title', explode(','), $csv);
        });
    }
}

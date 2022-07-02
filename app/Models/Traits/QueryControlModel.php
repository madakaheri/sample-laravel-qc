<?php

namespace App\Models\Traits;

trait QueryControlModel
{
    public function scopeQueryControl($query)
    {
        return $query
        ->when(request()->query('with'), function ($query, $csv) {
            return $query->with(explode(','), $csv);
        })
        ->when(request()->query('withCount'), function ($query, $csv) {
            return $query->withCount(explode(','), $csv);
        });
    }

    public function scopeQueryFilter($query)
    {
        return $query;
    }
}
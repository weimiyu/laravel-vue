<?php

namespace App\Filters;

class AdminPermissionFilter extends Filter
{
    protected $simpleFilters = [
        'id',
        'slug' => ['like', '%?%'],
        'name' => ['like', '%?%'],
        'http_path' => ['like', '%?%'],
    ];
}

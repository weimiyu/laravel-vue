<?php

namespace App\Utils;

class Admin
{
    /**
     * 当前登录管理员
     *
     * @return \App\Models\AdminUser|\Illuminate\Contracts\Auth\Authenticatable
     */
    public static function user()
    {
        return static::guard()->user();
    }

    /**
     * 当前登录管理员是不是超级管理员
     *
     * @return bool
     */
    public static function isAdministrator()
    {
        return static::user() && static::user()->isAdministrator();
    }

    /**
     * 把路径自动拼上后端的路径前缀
     *
     * @param string $path
     *
     * @return string
     */
    public static function url($path = '')
    {
        $prefix = 'admin-api';
        $path = trim($path, '/');

        if (is_null($path) || strlen($path) == 0) {
            $path = $prefix;
        } else {
            $path = $prefix.'/'.$path;
        }

        return $path;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard|\Tymon\JWTAuth\JWTGuard
     */
    public static function guard()
    {
        return auth('admin');
    }
}

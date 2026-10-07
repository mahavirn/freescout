<?php
/**
 * Laravel 5.5 global array_* and str_* helpers removed in Laravel 6.
 * Kept so existing code and modules keep working. New code should use Arr / Str.
 *
 * str_contains() is not defined here because PHP 8 has a native one
 * (it accepts a single string needle only, use Str::contains() for arrays).
 * array_first() / array_last() are native in PHP 8.5 without a callback argument,
 * use array_first_cond() or Arr::first() when passing a callback.
 */

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if (!function_exists('array_add')) {
    function array_add(...$args)
    {
        return Arr::add(...$args);
    }
}

if (!function_exists('array_collapse')) {
    function array_collapse(...$args)
    {
        return Arr::collapse(...$args);
    }
}

if (!function_exists('array_divide')) {
    function array_divide(...$args)
    {
        return Arr::divide(...$args);
    }
}

if (!function_exists('array_dot')) {
    function array_dot(...$args)
    {
        return Arr::dot(...$args);
    }
}

if (!function_exists('array_except')) {
    function array_except(...$args)
    {
        return Arr::except(...$args);
    }
}

if (!function_exists('array_first')) {
    function array_first(...$args)
    {
        return Arr::first(...$args);
    }
}

if (!function_exists('array_first_cond')) {
    function array_first_cond(...$args)
    {
        return Arr::first(...$args);
    }
}

if (!function_exists('array_flatten')) {
    function array_flatten(...$args)
    {
        return Arr::flatten(...$args);
    }
}

if (!function_exists('array_has')) {
    function array_has(...$args)
    {
        return Arr::has(...$args);
    }
}

if (!function_exists('array_last')) {
    function array_last(...$args)
    {
        return Arr::last(...$args);
    }
}

if (!function_exists('array_only')) {
    function array_only(...$args)
    {
        return Arr::only(...$args);
    }
}

if (!function_exists('array_pluck')) {
    function array_pluck(...$args)
    {
        return Arr::pluck(...$args);
    }
}

if (!function_exists('array_prepend')) {
    function array_prepend(...$args)
    {
        return Arr::prepend(...$args);
    }
}

if (!function_exists('array_random')) {
    function array_random(...$args)
    {
        return Arr::random(...$args);
    }
}

if (!function_exists('array_sort')) {
    function array_sort(...$args)
    {
        return Arr::sort(...$args);
    }
}

if (!function_exists('array_sort_recursive')) {
    function array_sort_recursive(...$args)
    {
        return Arr::sortRecursive(...$args);
    }
}

if (!function_exists('array_where')) {
    function array_where(...$args)
    {
        return Arr::where(...$args);
    }
}

if (!function_exists('array_wrap')) {
    function array_wrap(...$args)
    {
        return Arr::wrap(...$args);
    }
}

if (!function_exists('camel_case')) {
    function camel_case(...$args)
    {
        return Str::camel(...$args);
    }
}

if (!function_exists('ends_with')) {
    function ends_with(...$args)
    {
        return Str::endsWith(...$args);
    }
}

if (!function_exists('kebab_case')) {
    function kebab_case(...$args)
    {
        return Str::kebab(...$args);
    }
}

if (!function_exists('snake_case')) {
    function snake_case(...$args)
    {
        return Str::snake(...$args);
    }
}

if (!function_exists('starts_with')) {
    function starts_with(...$args)
    {
        return Str::startsWith(...$args);
    }
}

if (!function_exists('str_after')) {
    function str_after(...$args)
    {
        return Str::after(...$args);
    }
}

if (!function_exists('str_before')) {
    function str_before(...$args)
    {
        return Str::before(...$args);
    }
}

if (!function_exists('str_finish')) {
    function str_finish(...$args)
    {
        return Str::finish(...$args);
    }
}

if (!function_exists('str_is')) {
    function str_is(...$args)
    {
        return Str::is(...$args);
    }
}

if (!function_exists('str_limit')) {
    function str_limit(...$args)
    {
        return Str::limit(...$args);
    }
}

if (!function_exists('str_plural')) {
    function str_plural(...$args)
    {
        return Str::plural(...$args);
    }
}

if (!function_exists('str_random')) {
    function str_random(...$args)
    {
        return Str::random(...$args);
    }
}

if (!function_exists('str_replace_array')) {
    function str_replace_array(...$args)
    {
        return Str::replaceArray(...$args);
    }
}

if (!function_exists('str_replace_first')) {
    function str_replace_first(...$args)
    {
        return Str::replaceFirst(...$args);
    }
}

if (!function_exists('str_replace_last')) {
    function str_replace_last(...$args)
    {
        return Str::replaceLast(...$args);
    }
}

if (!function_exists('str_singular')) {
    function str_singular(...$args)
    {
        return Str::singular(...$args);
    }
}

if (!function_exists('str_slug')) {
    function str_slug(...$args)
    {
        return Str::slug(...$args);
    }
}

if (!function_exists('str_start')) {
    function str_start(...$args)
    {
        return Str::start(...$args);
    }
}

if (!function_exists('studly_case')) {
    function studly_case(...$args)
    {
        return Str::studly(...$args);
    }
}

if (!function_exists('title_case')) {
    function title_case(...$args)
    {
        return Str::title(...$args);
    }
}

// By reference.
if (!function_exists('array_forget')) {
    function array_forget(&$array, $keys)
    {
        Arr::forget($array, $keys);
    }
}

if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (!function_exists('array_pull')) {
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}

if (!function_exists('array_set')) {
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}

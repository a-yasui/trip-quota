<?php

if (! function_exists('str_random')) {
    /**
     * ランダムな文字列を生成
     */
    function str_random($length = 16)
    {
        return \Illuminate\Support\Str::random($length);
    }
}

<?php

if (! function_exists('suffix')) {
    /**
     * Adding string to end of target's value.
     *
     * @param $target
     * @param $suffix
     * @return string
     */
    function suffix($target, $suffix)
    {
        return $target.$suffix;
    }
}

if (! function_exists('file_get_php_classes')) {
    function file_get_php_classes($filepath)
    {
        $php_code = file_get_contents($filepath);
        $classes = get_php_classes($php_code);
        return $classes;
    }
}

if (! function_exists('get_php_classes')) {
    function get_php_classes($php_code)
    {
        $classes = [];
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        return $classes;
    }
}

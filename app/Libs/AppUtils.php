<?php

class AppUtils {

    public static function isActiveRoute($route, $output='active') {
        if (Route::currentRouteName() == $route) {
            return $output;
        }
    }
}
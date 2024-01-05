<?php
/**
 * Created by PhpStorm.
 * Date: 7/18/18
 * Time: 3:41 PM
 */

namespace App\Repositories;


class Data
{
    public static function isValid($get, $key) {
        if (isset($get[$key])) {
            $var = $get[$key];

            if (!empty($var) && !is_null($var) && $var != 'null')
                return true;
        }

        return false;
    }
}

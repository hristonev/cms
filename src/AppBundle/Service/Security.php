<?php
/**
 * Created by PhpStorm.
 * User: dimitar
 * Date: 24.08.17
 * Time: 10:43
 */

namespace AppBundle\Service;


class Security
{
    public static function getRandomPassword()
    {
        return base64_encode(random_bytes(10));
    }
}
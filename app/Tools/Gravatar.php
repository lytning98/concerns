<?php

namespace App\Tools;


use App\User;
use App\Models\Person;

/**
 * Class Gravatar
 * @package App\Tools
 * fetch Gravatar photo by email or User model
 */
class Gravatar
{

    public static function getURLbyEmail($email, $size)
    {
        return "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?r=g&s=" . $size;
    }

    public static function getURLbyUser(User $user, $size)
    {
        return Gravatar::getURLbyEmail($user->email, $size);
    }

    public static function getURLbyPerson(Person $per, $size)
    {
        return Gravatar::getURLbyEmail($per->email, $size);
    }
}
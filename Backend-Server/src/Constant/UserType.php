<?php

namespace App\Constant;

enum UserType: string
{
    case SuperAdmin = "ADMIN";
    case User = "USER";

}
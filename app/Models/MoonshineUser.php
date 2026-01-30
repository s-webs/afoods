<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use MoonShine\Laravel\Models\MoonshineUser as BaseMoonshineUser;

class MoonshineUser extends BaseMoonshineUser
{
    use HasApiTokens;
}

<?php

namespace App\Models;

use App\Traits\HasDateTimeFormatterTrait;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasDateTimeFormatterTrait;

    protected $table = 'categories';
}

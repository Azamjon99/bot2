<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
class Trip extends Model implements TranslatableContract
{
    use Translatable;
    public $translatedAttributes = ['name'];
    use HasFactory;
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    protected $table = 'words';
    protected $primaryKey = 'id';
    public $timestamps = false;

}
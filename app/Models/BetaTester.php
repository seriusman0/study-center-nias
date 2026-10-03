<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetaTester extends Model
{
    protected $fillable = ['email', 'whatsapp', 'status'];
}

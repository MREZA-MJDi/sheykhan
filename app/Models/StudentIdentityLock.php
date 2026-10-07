<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentIdentityLock extends Model
{
    use HasFactory;

    protected $fillable = ['national_id_lookup', 'user_id'];
}
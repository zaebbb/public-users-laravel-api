<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id_left',
        'user_id_left_message',
        'user_id_right',
        'user_id_right_message'
    ];
}

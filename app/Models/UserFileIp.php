<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFileIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_ip',

    ];
    protected $table = 'file_user_ip';
}

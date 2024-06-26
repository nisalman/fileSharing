<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'filenames',
        'status',
        'user_id',
        'size',
        'last_download_date',
        'link',
        'request',
        'comment',

    ];
}

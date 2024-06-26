<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'file_id',
        'comment',
        'email',
    ];
    protected $table = 'file_user_share';
    public function File()
    {
        return $this->hasMany(File::class);
    }
}

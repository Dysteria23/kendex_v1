<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    const UPDATED_AT = null;

    protected $table = 'notifications';

    protected $fillable = ['user_id', 'type', 'message', 'url', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}

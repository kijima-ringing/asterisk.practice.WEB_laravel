<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PjsipAuth extends Model
{
    protected $table = 'ps_auths';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id',
        'auth_type',
        'password',
        'username',
        'created_at',
        'updated_at'
    ];

    public function endpoint()
    {
        return $this->belongsTo(PjsipEndpoint::class, 'id', 'auth');
    }
}
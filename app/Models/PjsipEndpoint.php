<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PjsipEndpoint extends Model
{
    protected $table = 'ps_endpoints';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id',
        'context',
        'disallow',
        'allow',
        'auth',
        'aors',
        'rewrite_contact',
        'mailboxes',
        'transport',
        'created_at',
        'updated_at'
    ];

    public function auth()
    {
        return $this->hasOne(PjsipAuth::class, 'id', 'auth');
    }

    public function aor()
    {
        return $this->hasOne(PjsipAor::class, 'id', 'aors');
    }
}
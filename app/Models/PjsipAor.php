<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PjsipAor extends Model
{
    protected $table = 'ps_aors';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id',
        'type',
        'max_contacts',
        'created_at',
        'updated_at'
    ];

    public function endpoint()
    {
        return $this->belongsTo(PjsipEndpoint::class, 'id', 'aors');
    }
}

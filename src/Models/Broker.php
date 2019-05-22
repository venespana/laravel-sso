<?php

namespace Venespana\Sso\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'id'
    ];

    public function setNameAttribute(string $name)
    {
        $this->attributes['name'] = $name;

        $this->attributes['hash'] = uniqid('', true);
        $this->attributes['secret'] = Str::random(32);
    }
    
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config('sso.broker_table', 'brokers');
    }
}

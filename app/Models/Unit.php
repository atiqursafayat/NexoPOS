<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model 
{
    protected $table = 'nexopos_units';
    protected $casts    =   [
        'base_unit'     =>  'boolean'
    ];

    public function group()
    {
        return $this->belongsTo( UnitGroup::class, 'group_id' );
    }
} 
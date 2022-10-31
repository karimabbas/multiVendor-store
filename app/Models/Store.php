<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    // protected $connection = 'mysql';
    // protected $table = 'stores';
    // protected $primaryKey = 'id';
    // protected $keyType = 'int';
    // public $incrementing = true;
    // public $timestamps = true;

    //// if changed timestamps default name(created_at/updated_at) in table columns you need to:
    // const CREATED_AT ='created_on';
    // const UPDATED_AT = 'updated_on';

    public function products()
    {

        return $this->hasMany(Product::class, 'store_id', 'id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShortList extends Model
{
    protected $table='short_lists';
    protected $primaryKey='id';
    protected $fillable=['user_id','property_id'];

    public function property(){

        return $this->hasOne(Property::class,'id','property_id');
        
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Modele extends Model
{
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];
    //Return the brand owned
    public function brand() {
        return $this->belongsTo('App\Brand');
    }

}

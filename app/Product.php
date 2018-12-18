<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $guarded = []; //Allow all fields as fillable

    
    //Return the modele owned
    public function modele() {
        return $this->belongsTo('App\Modele');
    }
}

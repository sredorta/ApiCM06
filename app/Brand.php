<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class Brand extends Model
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
    //Return the attachments if any
    public function attachments() {
        return $this->morphMany(Attachment::class,'attachable');
    }

    //Return the models
    public function modeles() {
        return $this->hasMany('App\Modele');
    }

}

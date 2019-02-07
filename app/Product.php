<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Attachment;

class Product extends Model
{
    public $guarded = []; //Allow all fields as fillable


    //Return the modele owned
    public function modele() {
        return $this->belongsTo('App\Modele');
    }

    //Return the attachments if any
    public function attachments() {
        return $this->morphMany(Attachment::class,'attachable');
    }   
    
    //Override delete function to remove attachments if any before
    public function delete() {
        foreach ($this->attachments()->get() as $attachment) {
            $attachment->remove();
        }
        return parent::delete();
    }
}

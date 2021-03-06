<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Attachment;

class User extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstName','lastName', 'email', 'mobile','isEmailValidated','emailValidationKey','language'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'emailValidationKey',
    ];


    //Return the accounts of the user
    public function accounts() {
        return $this->hasMany('App\Account');
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

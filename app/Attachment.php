<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\kubiikslib\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use App\Thumb;
use finfo;

class Attachment extends Model
{
    protected $guarded = []; //Allow all fields as fillable
    protected $hidden = ['attachable_type','attachable_id'];

    private $myFile = null;

    public function attachable() {
        return $this->morphTo();
    }
    //Return the thumbs of the attachable
    public function thumbs() {
        return $this->hasMany('App\Thumb');
    }

    //Returns the extension of the file depending on the mime
    private function getBase64Extension($base64) {
        //All images are given in jpeg   format by front-end
        if (preg_match('/image\/png/', $base64)) {
            return 'png';
        }
        //TODO add documents and other types
    }
    private function getFileMime() {
        return Storage::disk('public')->mimeType('/uploads/' . $this->file_name);
    }

    //Get URL of file
    private function getFileUrl() {
        return Storage::disk('public')->url('/uploads/' . $this->file_name);
    }

    //Get file size
    private function getFileSize() {
        return Storage::disk('public')->size('/uploads/' . $this->file_name);
    }

    //Store the file from a base64
    public function storeBase64($base64) {
        list($baseType, $image) = explode(';', $base64);
        list(, $image) = explode(',', $image);
        $image = base64_decode($image);
        $extension = $this->getBase64Extension($baseType);
        $imageName = rand(111111111, 999999999) . '.' . $extension;
        $exists = Storage::disk('public')->exists('uploads/'.$imageName);
        //Make sure it doesn't exist already
        while ($exists) {
            $imageName = rand(111111111, 999999999) . '.png';
        }
        $p = Storage::disk('public')->put('uploads/' . $imageName, $image, 'public');
        if (!$p) {
            return response()->json(['response'=>'error', 'message'=>__('attachment.save_error')], 400);
        }     
        $this->file_name = $imageName;
        $this->file_extension = $extension;
        $this->mime_type = $this->getFileMime();
        $this->url = $this->getFileUrl();
        $this->file_size = $this->getFileSize();
    }
 


    //Returns the file path from the public disk
    public function getPath() {
        return str_replace(Storage::disk('public')->url(''), '', $this->url);
    }
    //Returns the relative path of the file
    public function getRelativePath() {
        $str = str_replace(Storage::disk('public')->url(''), '', $this->url);
        $str = str_replace($this->file_name, '', $str);
        return $str;
    }   

    //Create thumbnails if is image and if not defaults
    private function createThumbs() {
        if (preg_match('/image/', $this->mime_type)) {
            Thumb::add($this->id);
        }
    }

    //Save the register and create the thumbnails if required
    public function save(array $options = []) {
/*        if (preg_match('/image/', $this->mime_type)) {
            $this->isImage = true;
            $manager = new ImageManager(array('driver' => 'gd'));
            $image = Storage::disk('public')->get($this->myFile);
            $imageOrig = $manager->make($image); 
            //Get width and height of the image
            $this->img_width = $imageOrig->width();
            $this->img_height = $imageOrig->height();
        }*/
        parent::save($options);
        $this->createThumbs();
    }

    //Delete an attachable register and delete the associated data
    public function remove() {
        //Check if there are thumbs and delete files and db
        foreach ($this->thumbs()->get() as $thumb) {
            $thumb->remove();
        }        
        //Delete the attachable itself only if is not default
        if (strpos($this->url, '/defaults/') === false) {
            Storage::disk('public')->delete($this->getPath());
        }
        parent::delete();    //Remove db record
    }

}

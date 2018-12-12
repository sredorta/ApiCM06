<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Brand;
use App\Attachment;
use Validator;

class BrandController extends Controller
{

    private function outputBrand($brand, $size) {
        $attachment = $brand->attachments()->get()->first();
        $thumb = $attachment->thumbs()->where('size', $size)->first();
        if ($thumb) {
            unset($thumb->id);
            unset($thumb->attachment_id);
            unset($thumb->created_at);
            unset($thumb->updated_at);
            $thumb->alt_text = $attachment->alt_text;
            $brand->image = $thumb;
        } else {
            unset($thumb);
            $thumb = (object)[];
            $thumb->url = $attachment->url;
            $thumb->size = "full";
            $thumb->width = $attachment->img_width;
            $thumb->height = $attachment->img_height;
            $thumb->file_size = $attachment->file_size;
            $thumb->alt_text = $attachment->alt_text;
            $brand->image = $thumb;
        }
        return $brand;        
    }

    //Return our messages
    public function getAll(Request $request) {
        $validator = Validator::make($request->all(), [
            'size'   => 'in:full,large,big,medium,small,thumbnail,tinythumbnail',
        ]);
        $result = [];
        foreach (Brand::all() as $brand) {
            array_push($result, $this->outputBrand($brand, $request->size));
        }
        return response()->json($result,200);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|unique:brands|min:2|max:100',
            'image' => 'nullable|mimes:jpeg,jpg,bmp,png,gif,svg|max:2048',
            'size' => 'in:full,large,big,medium,small,thumbnail,tinythumbnail'
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }                       
        $brand = Brand::create(['name' => $request->name]);
       //We now create the Attachable with the image uploaded
       $attachment = new Attachment;
       $attachment->attachable_id = $brand->id;
       $attachment->attachable_type = Brand::class;
       $response = $attachment->getTargetFile($request->file('image'), "brand");
       if ($response !== null) {
           return response()->json(['response'=>'error', 'message'=>__('attachment.default', ['default' => $request->default])], 400);
       }
       $attachment->alt_text = "Logo marque";
       $attachment->title = "No title";
       $attachment->description = "No description";
       $attachment->save(); //save and generate thumbs

       return response()->json($this->outputBrand($brand, $request->size),200);  
    }

/*
    //Return our messages
    public function markAsRead(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|exists:messages,id'
        ]);      
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }      
        $user = User::find($request->get("myUser"));
        $user->messages()->where("id", $request->id)->update(['isRead' => true]);

        return response()->json([],204);
    }    
*/
    //Delete message
    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|exists:brands,id'
        ]);      
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }      
        $brand = Brand::find($request->get("id"));
        $brand->delete();

        return response()->json([],204);
    }   



}

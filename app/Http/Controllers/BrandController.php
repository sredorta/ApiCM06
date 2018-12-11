<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Brand;
use App\Attachment;
use Validator;

class BrandController extends Controller
{

    //Return our messages
    public function getAll(Request $request) {
        $result = [];
        foreach (Brand::all() as $brand) {
            $brand->tumbs = $brand->attachments()->get()->first()->thumbs()->get();
            $brand->image = $brand->attachments()->get()->first();
            array_push($result, $brand);
        }
        return response()->json($result,200);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|unique:brands|min:2|max:100',
            'description' => 'required|min:2|max:500',
            'image' => 'nullable|mimes:jpeg,jpg,bmp,png,gif,svg|max:2048'
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }                       
        $brand = Brand::create(['name' => $request->name , 'description' => $request->description]);
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

       //Return data
       $brand->tumbs = $brand->attachments()->get()->first()->thumbs()->get();
       $brand->image = $brand->attachments()->get()->first();

       return response()->json($brand,200);  
//       return $brand->with('attachments')->toArray();
//        return response()->json(['response'=>'success', 'message'=>'this is a success test'], 200);
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

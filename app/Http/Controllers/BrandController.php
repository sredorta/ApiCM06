<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Brand;
use App\Attachment;
use Validator;

class BrandController extends Controller
{

    private function outputBrand($brand) {
        //Dump all product with attachments
        $attachments = [];
        foreach ($brand->attachments()->where('type','logo')->get() as $attachment) {
            //Convert thumbs by indexing by size
            $mythumbs = [];
            foreach ($attachment->thumbs()->get() as $thumb) {
                $mythumbs[$thumb->size] = $thumb->toArray();
                unset($mythumbs[$thumb->size]['size']);
            }
            $attachment->sizes = $mythumbs;
            array_push($attachments, $attachment->toArray());
        }
        if (array_key_exists(0,$attachments))
            $brand->image = $attachments[0];
        return $brand;        
    }

    //Return our messages
    public function getAll(Request $request) {
        $result = [];
        foreach (Brand::orderBy('name')->get() as $brand) {
            array_push($result, $this->outputBrand($brand));
        }
        return response()->json($result,200);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|unique:brands,name|min:2|max:100',
            'image' => 'nullable|regex:/data:.*jpeg;base64/'
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }                       
        $brand = Brand::create(['name' => $request->name]);
       //We now create the Attachable with the image uploaded
       if ($request->image) {
            $attachment = new Attachment;
            $attachment->attachable_id = $brand->id;
            $attachment->attachable_type = Brand::class;
            $attachment->storeBase64($request->image); 
            $attachment->alt_text = "Logo " . $brand->name;
            $attachment->type = "logo";  //Set type of attachment to logo
            $attachment->save();
            //dd($attachment->toArray());
       }
       return response()->json($this->outputBrand($brand),200);  
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:brands,id',
            'name'   => 'required|unique:brands,name,'.$request->id.',id|min:2|max:100',
            'image' => 'nullable|regex:/data:.*jpeg;base64/'
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }               
            
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->update();

        if ($request->image !== null) {
            //Delete previous attachment
            foreach($brand->attachments()->get() as $attachment) {
                $attachment->remove();
            }
            //We now create the Attachable with the image uploaded
            $attachment = new Attachment;
            $attachment->attachable_id = $brand->id;
            $attachment->attachable_type = Brand::class;
            $attachment->storeBase64($request->image); 
            $attachment->alt_text = "Logo " . $brand->name;
            $attachment->type = "logo";  //Set type of attachment to logo
            $attachment->save();
        }
       return response()->json($this->outputBrand($brand, $request->size),200);  
    }

    //Delete
    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|exists:brands,id'
        ]);      
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }      
        $brand = Brand::find($request->get("id"));
        //Delete all attachments and thumbs
        foreach($brand->attachments()->get() as $attachment) {
            $attachment->remove();
        }
        $brand->delete();
        return response()->json([],204);
    }   



}

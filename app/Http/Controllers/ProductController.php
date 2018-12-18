<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Attachment;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{

    private function outputBrand($brand, $size) {
        $attachment = $brand->attachments()->get()->first();
        $thumb = $attachment->thumbs()->where('size', $size)->first();
        if ($thumb) {
            unset($thumb->id);
            unset($thumb->attachment_id);
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
        foreach (Brand::orderBy('name')->get() as $brand) {
            array_push($result, $this->outputBrand($brand, $request->size));
        }
        return response()->json($result,200);
    }

    public function create(Request $request) {
        //$request->images = json_decode($request->images);
        $validator = Validator::make($request->all(), [
            'model_id'      => 'required|exists:modeles,id',
            'title'         => 'required|min:2|max:100',
            'description'   => 'required|min:2|max:500',
            'price'         => 'required|numeric|min:0',
            'discount'      => 'required|numeric|min:0|max:'.$request->price,
            'stock'         => 'required|numeric|min:0',
            'isVehicle'     => 'in:0,1,true,false',
            "images"        => 'required|array',
            //"image.*"      => 'mimes:jpeg,jpg,bmp,png,gif,svg|max:2048'
/*
            'images' => 'nullable|mimes:jpeg,jpg,bmp,png,gif,svg|max:2048',
            'size' => 'in:full,large,big,medium,small,thumbnail,tinythumbnail'*/
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }     
                  
        if ($request->isVehicle == "false") $request->isVehicle = false;
        else $request->isVehicle = true;
        $product = Product::create(['modele_id'=> intval($request->model_id), 
                                    'title' => $request->title,
                                    'description' => $request->description,
                                    'price' => intval($request->price),
                                    'discount' => intval($request->discount),
                                    'stock' => intval($request->stock),
                                    'isVehicle' => $request->isVehicle]);
        //dd($product->toArray());
        //Now add the attachments
        foreach ($request->images as $base64) {
            //Store the file in uploads
            list($baseType, $image) = explode(';', $base64);
            list(, $image) = explode(',', $image);
            $image = base64_decode($image);
            $imageName = rand(111111111, 999999999) . '.jpg';
            $exists = Storage::disk('public')->exists('uploads/'.$imageName);
            //Make sure it doesn't exist already
            while ($exists) {
                $imageName = rand(111111111, 999999999) . '.jpg';
            }
            $p = Storage::disk('public')->put('uploads/' . $imageName, $image, 'public');
            if (!$p) {
                //TODO TRANSLATE !!!!
                return response()->json(['response'=>'error', 'message'=>__('image_processing_error')], 400);
            } else {
                $file = Storage::disk('public')->path('uploads/' . $imageName);
                echo basename($file);
                /*$attachment = new Attachment;
                $attachment->attachable_id = $product->id;
                $attachment->attachable_type = Product::class;
                $attachment->setFile(); //TODO !!!!!!
                $attachment->alt_text ="Photo " . $product->title;
                $attachment->title = "No title";
                $attachment->description = "No description";
                $attachment->save();*/

            }

        }

       //We now create the Attachable with the image uploaded
/*       $attachment = new Attachment;
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
*/
       return response()->json($product,200);  
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:brands,id',
            'name'   => 'required|unique:brands,name,'.$request->id.',id|min:2|max:100',
            'image' => 'nullable|mimes:jpeg,jpg,bmp,png,gif,svg|max:2048',
            'size' => 'in:full,large,big,medium,small,thumbnail,tinythumbnail'
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
            $response = $attachment->getTargetFile($request->file('image'), "brand");
            if ($response !== null) {
                return response()->json(['response'=>'error', 'message'=>__('attachment.default', ['default' => $request->default])], 400);
            }
            $attachment->alt_text = "Logo marque";
            $attachment->title = "No title";
            $attachment->description = "No description";
            $attachment->save(); //save and generate thumbs
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

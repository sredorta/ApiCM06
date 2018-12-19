<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Attachment;
use App\Thumb;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{

    private function outputProduct($product) {
        //Dump all product with attachments
        $attachments = [];
        foreach ($product->attachments()->where('type','gallery')->get() as $attachment) {
            //Convert thumbs by indexing by size
            $mythumbs = [];
            foreach ($attachment->thumbs()->get() as $thumb) {
                $mythumbs[$thumb->size] = $thumb->toArray();
                unset($mythumbs[$thumb->size]['size']);
            }
            $attachment->sizes = $mythumbs;
            array_push($attachments, $attachment->toArray());
        }
        //if (array_key_exists(0,$attachments))
        $product->images = $attachments;
        $model = $product->modele;
        $product->model = $model->name;
        $product->brand = $model->brand->name;
        $product->model_id = $product->modele->id;
        $product->brand_id = $model->brand->id;
        unset($product->modele);
        unset($product->modele_id);
        /*dd($product->toArray());*/
        //    $product->brand = $product->
        return $product;        
    }

    //Return all products
    public function getAll(Request $request) {
        $result = [];
        foreach (Product::all() as $product) {
            array_push($result, $this->outputProduct($product));
        }
        return response()->json($result,200);
    }


    //Create a product
    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'model_id'      => 'required|exists:modeles,id',
            'title'         => 'required|min:2|max:100',
            'description'   => 'nullable|min:2|max:500',
            'price'         => 'required|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0|max:'.$request->price,
            'stock'         => 'required|numeric|min:0',
            'isVehicle'     => 'required|boolean',
            "images"        => 'nullable|array',
            "images.*"      => 'required_with:images|regex:/data:.*;base64/' 
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }     

        $product = Product::create(['modele_id'=> $request->model_id, 
                                    'title' => $request->title,
                                    'description' => $request->description,
                                    'price' => $request->price,
                                    'discount' => $request->discount,
                                    'stock' => $request->stock,
                                    'isVehicle' => $request->isVehicle]);
        //Now add the attachments
        foreach ($request->images as $base64) {
            $attachment = new Attachment;
            $attachment->attachable_id = $product->id;
            $attachment->attachable_type = Product::class;
            $attachment->storeBase64($base64); 
            $attachment->alt_text ="Photo " . $product->title;
            $attachment->type = "gallery";  //Set type of attachment to gallery
            $attachment->save();
        }

       //We now create the Attachable with the image uploaded
       return response()->json($this->outputProduct($product),200);  
    }

    //TODO !!!!!!
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

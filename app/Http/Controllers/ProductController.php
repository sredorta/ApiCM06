<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Attachment;
use App\brand;
use App\Thumb;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Config;

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
        if (Brand::find($model->brand)->first()->attachments()->get()->count()>0) {
            $product->brand_url = Brand::find($model->brand)->first()->attachments()->first()->thumbs()->where('size','tinythumbnail')->first()->toArray()['url'];
        } else {
            $product->brand_url = "";
        }
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
    //Return all products
    public function get(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'            => 'required|exists:products,id' 
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        } 
        return response()->json($this->outputProduct(Product::find($request->id)),200);
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
            "images.*"      => 'required_with:images|regex:/data:image\/jpeg;base64/' 
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
        if (!is_null($request->images)) {
            foreach ($request->images as $base64) {
                $attachment = new Attachment;
                $attachment->attachable_id = $product->id;
                $attachment->attachable_type = Product::class;
                $attachment->storeBase64($base64); 
                $attachment->alt_text ="Photo " . $product->title;
                $attachment->type = "gallery";  //Set type of attachment to gallery
                $attachment->save();
            }
        }

       //We now create the Attachable with the image uploaded
       return response()->json($this->outputProduct($product),200);  
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'            => 'required|exists:products,id',
            'title'         => 'required|min:2|max:100',
            'description'   => 'nullable|min:2|max:500',
            'price'         => 'required|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0|max:'.$request->price,
            'stock'         => 'required|numeric|min:0',
            'isVehicle'     => 'required|boolean',
            "images"        => 'nullable|array',
            "images.*"      => 'required_with:images|regex:/data:image\/jpeg;base64/' 
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }               
            
        $product = Product::find($request->id);
        $product->title         = $request->title;
        $product->description   = $request->description;
        $product->price         = $request->price;
        $product->discount      = $request->discount;
        $product->stock         = $request->stock;
        $product->isVehicle     = $request->isVehicle;
        $product->update();

        //Delete previous attachment
        foreach($product->attachments()->get() as $attachment) {
            $attachment->remove();
        }
        //Now add the attachments
        if (!is_null($request->images)) {
                foreach ($request->images as $base64) {
                    $attachment = new Attachment;
                    $attachment->attachable_id = $product->id;
                    $attachment->attachable_type = Product::class;
                    $attachment->storeBase64($base64); 
                    $attachment->alt_text ="Photo " . $product->title;
                    $attachment->type = "gallery";  //Set type of attachment to gallery
                    $attachment->save();
                }
        }      
        return response()->json($this->outputProduct($product),200);  
    }

    //Delete
    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|exists:products,id'
        ]);      
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }      
        $product = Product::find($request->get("id"));
        //Delete all attachments and thumbs
        foreach($product->attachments()->get() as $attachment) {
            $attachment->remove();
        }
        $product->delete();
        return response()->json([],204);
    }   




}

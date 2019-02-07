<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Brand;
use App\Modele;
use App\Product;
use Validator;

class ModeleController extends Controller
{

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|exists:brands,id',
            'name' => 'required|min:2|max:100'
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }                     
        $brand = Brand::find($request->id);  
        $modele = $brand->modeles()->create(['name' => $request->name]);
        return response()->json($modele,200);  
    }

    public function getAll(Request $request) {          
        $result = [];
        foreach(Brand::with('modeles')->get() as $brand) {
            foreach($brand->modeles()->get() as $modele) {
                array_push($result, $modele);
            }
        }
        return response()->json($result,200);
    }

    //Delete
    public function delete(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|exists:modeles,id'
        ]);      
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }      
        $modele = Modele::find($request->get("id"));
        foreach ($modele->products()->get() as $product) {
            $product->delete();
        }
        $modele->delete();
        return response()->json([],204);
    }

    //Update
    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:modeles,id',
            'name'   => 'required|unique:modeles,name,'.$request->id.',id|min:2|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }               
            
        $modele = Modele::find($request->id);
        $modele->name = $request->name;
        $modele->update();
        return response()->json($modele,200);  
    }    


}

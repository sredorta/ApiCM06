<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Configuration;
use App\Product;
use App\Order;
use Validator;
class OrderController extends Controller
{


    //Checks if cart is valid to update the gui
    public function checkCart(Request $request) {
        $validator = Validator::make($request->all(), [
            'cart'  => 'required|array',
            'cart.*.id' => 'required|numeric',
            'cart.*.quantity'=> 'required|numeric'

        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }
        //Check that all product exists and that we got enough stock
        $cart = $request->cart;
        $cart = $this->_purgeCart($cart);
        //Generate the result
        $result = (object)[];
        $result->price              = $this->_getCartPrice($cart);
        $result->isWeightExceeded   = $this->_isWeightExceeded($cart);
        $result->deliveryCost       = $this->_getDeliveryPrice($cart, $request->delivery);
        $result->cart = $this->_getCartResult($cart);
      
        return response()->json($result,200);  
    }


    //Checks if order is valid and return all data for gui
    //It does not create the order
    public function check(Request $request) {

        $validator = Validator::make($request->all(), [
            'firstName' => 'required|min:2|max:50',
            'lastName' => 'required|min:2|max:50',
            'email' => 'required|email',            
            'mobile' => 'required|regex:/^[0-9]+$/|min:10|max:10',
            'delivery'  => 'required|boolean',
            'address1'  => 'required_if:delivery,true|min:2|max:200',
            'address2'  => 'nullable|min:2|max:200',
            'cp'        => 'required_if:delivery,==,true|regex:/^[0-9]+$/|min:5|max:5',
            'city'      => 'required_if:delivery,==,true|min:2|max:100',
            'cart'  => 'required|array',
            'cart.*.id' => 'required|numeric',
            'cart.*.quantity'=> 'required|numeric'

        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }
        //Check that all product exists and that we got enough stock
        $cart = $request->cart;
        $cart = $this->_purgeCart($cart);
        //Generate the result
        $result = (object)[];
        $result->price              = $this->_getCartPrice($cart);
        $result->isWeightExceeded   = $this->_isWeightExceeded($cart);
        $result->deliveryCost       = $this->_getDeliveryPrice($cart, $request->delivery);
        $result->cart = $this->_getCartResult($cart);
        $result->total              = $result->price + $result->deliveryCost;
      
        return response()->json($result,200);  
    }









    //Creates an object array with all elements for the cart
    private function _getCartResult($cart) {
        $result = [];
        foreach($cart as $item){
            $obj = (object)[];
            $product = Product::find($item['id']);
            $obj->id = $product->id;
            $obj->title = $product->title;
            $obj->stock = $product->stock;
            $obj->weight = $product->weight;
            $obj->price = $product->price - $product->discount;
            $obj->url = $this->_getUrl($product->id);
            $obj->quantity = $item['quantity'];
            $obj->tprice = $obj->price * $obj->quantity;
            array_push($result, $obj);
        }
        return $result;        
    }

    //Gets medium size url of the product
    private function _getUrl($id) {
        if (Product::find($id)->attachments()->get()->count()>0) {
            return Product::find($id)->attachments()->first()->thumbs()->where('size','medium')->first()->toArray()['url'];
        } else {
            return  "";
        }
    }



    //Checks the cart and updates it if any products are not existing or stock is too high
    private function _purgeCart($cart) {
        $result = [];
        foreach($cart as $item){
            $product = Product::find($item['id']);
            if ($product) {   //Remove product from cart if it doesn't exist
                if ($product->stock < $item['quantity']) {
                    $item['quantity'] = $product->stock;
                } 
                array_push($result, $item);
            }
        }
        return $result;
    }

    //Returns cart price without delivery
    private function _getCartPrice($cart) {
        $result = 0;
        foreach($cart as $item){
            $product = Product::find($item['id']);
            $result = $result + ($product->price - $product->discount)*$item['quantity'];
        }
        return $result;        
    }

    //Returns the delivery price
    private function _getDeliveryPrice($cart, $delivery) {        
        if ($delivery == false) return 0;
        $weight = $this->_getCartWeight($cart,$delivery);
        if ($weight <= 2)  return Configuration::where('key','delivery1')->first()->value;
        if ($weight <= 10) return Configuration::where('key','delivery2')->first()->value;
        if ($weight <= 30) return Configuration::where('key','delivery3')->first()->value;
        return 0;
    }

    

    //Calculates the cart total weight
    private function _getCartWeight($cart) {
        $result = 0;
        foreach($cart as $item){
            $product = Product::find($item['id']);
            $result = $result + ($product->weight * $item['quantity']);
        }
        return $result;          
    }

    //Checks if weight exceeds 30kg
    private function _isWeightExceeded($cart) {
        $weight = $this->_getCartWeight($cart);
        if ($weight>30) {
            return true;
        }
        return false;
    }



}

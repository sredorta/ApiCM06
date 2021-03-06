<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Configuration;
use App\Product;
use App\Order;
use App\User;
use Validator;
use App\kubiikslib\EmailTrait;
use Illuminate\Support\Facades\Config;

/*Stripe payment*/
use Stripe;
use Stripe\PaymentIntent;
//use Stripe\StripePaymentIntent;
//use Stripe\Error\Card;



class OrderController extends Controller
{
    use EmailTrait;                //Add email traits

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
/*            'firstName' => 'required|min:2|max:50',
            'lastName' => 'required|min:2|max:50',
            'email' => 'required|email',            
            'mobile' => 'required|regex:/^[0-9]+$/|min:10|max:10',
            'delivery'  => 'required|boolean',
            'address1'  => 'required_if:delivery,true|min:2|max:200',
            'address2'  => 'nullable|min:2|max:200',
            'cp'        => 'required_if:delivery,==,true|regex:/^[0-9]+$/|min:5|max:5',
            'city'      => 'required_if:delivery,==,true|min:2|max:100',*/
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
                if ($product->stock>0) {
                    array_push($result, $item);
                }
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
        if ($delivery === false) return 0;
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


    //Creates an object array with all elements for the cart stored in orders
    //It returns a json string
    private function _cartToJson($cart) {
        $result = [];
        foreach($cart as $item){
            $obj = (object)[];
            $product = Product::find($item['id']);
            $obj->id = $product->id;
            $obj->title = $product->title;
            $obj->price = $product->price - $product->discount;
            $obj->weight = $product->weight;
            $obj->quantity = $item['quantity'];
            $obj->tprice = $obj->price * $obj->quantity;
            array_push($result, $obj);
        }
        return json_encode($result);        
    }

    private function _cartFromJson($cart) {
        return json_decode($cart);
    }

    //Gets the orders related to auth user
    public function getAuthOrders(Request $request) {
        $user = User::find($request->get('myUser'));
        $orders = Order::orderBy('id', 'DESC')->where("user_id", $user->id)->get();
        return json_encode($orders);
    }

    //Gets all orders
    public function getOrders(Request $request) {
        $user = User::find($request->get('myUser'));
        $orders = Order::orderBy('id', 'DESC')->get();
        return json_encode($orders);
    }
    //Gets count of orders
    public function getCount(Request $request) {
        return json_encode($orders = Order::where("status", "paiement accepté")->count());
    }

    //Update status field
    public function updateStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders',
            'status' => 'required|min:2|max:50',
            'tracking' => 'nullable|min:11|max:15'

        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }
        $order = Order::find($request->id);
        $order->status = $request->status;
        if ($request->tracking) {
            $order->tracking = $request->tracking;
        } else {
            $order->tracking = null;
        }
        $order->update();
        $cart = json_decode($order->cart);
        //Send email with updated status
        //Now we need to send email to user 
        $html = "<div>
        <h2>" . __('email.order_change_title', ['status'=>$order->status]) . "</h2>
        <h3>" . __('email.order_total', ['total'=>$order->total]) . "</h3>
        <h4>" . __('email.order_reference', ['reference'=>$order->id]) . "</h4>";
        if (!$order->delivery) {
            $html = $html . "<p>" . __('email.order_nodelivery') . "</p>";
        } else {
            
            if (strlen($order->tracking>5)) {
                $html = $html . "
                <h4>" . __('email.order_colissimo_title', ['tracking'=>$order->tracking]) . "</h4>
                <a href='https://www.laposte.fr/particulier/outils/suivre-vos-envois?code=" . $order->tracking . "'>" . __('email.order_colissimo_click') . "</a>";
            }       
            $html = $html . "<h4>" . __('email.order_delivery') . "</h4>";         
            $html = $html . "<p>" . $order->address1 . "</p>
                             <p>" . $order->address2 . "</p> 
                             <p>" . $order->cp . "</p> 
                             <p>" . $order->city . "</p>
                             <p>FRANCE</p>"; 
        }
        $html = $html . "<h4>" . __('email.order_products') . "</h4>";
        foreach($cart as $item) {
            //$product = Product::find($item->id);
            $html = $html . "<p>" . $item->quantity . " x   " . $item->title . "</p>"; 
        }
        $html = $html . "</div>";
        $html = $html . "</div>";
        $data = ['html' => $html];
        $this->sendEmail($order->email, __('email.order_subject'), $data);


        return response()->json($order,200);  

    }

    //Delete specific order
    public function deleteOrder(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders'
        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }
        $order = Order::find($request->id);
        $order->delete();
        return response()->json([],204);  

    }    



    private function _updateProducts($cart) {
        foreach($cart as $item){
            $obj = (object)[];
            $product = Product::find($item->id);
            $stock = $product->stock - $item->quantity;
            if ($stock<0) $stock = 0;
            $product->stock = $stock;
            $product->update();
        }
    }

    public function createPreOrder(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'nullable|exists:users,id',
            'firstName' => 'required|min:2|max:50',
            'lastName' => 'required|min:2|max:50',
            'email' => 'required|email',            
            'mobile' => 'required|regex:/^[0-9]+$/|min:10|max:10',
            'delivery'  => 'required|boolean',
            'address1'  => 'required_if:delivery,1',
            'address2'  => 'nullable',
            'cp'        => 'required_if:delivery,1',
            'city'      => 'required_if:delivery,1',
            'total' => 'required|numeric',
            'cart'  => 'required|array',
            'cart.*.id' => 'required|numeric',
            'cart.*.title' => 'required|min:2',
            'cart.*.quantity'=> 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }
        if ($request->delivery) {
            $validator = Validator::make($request->all(), [
                'address1'  => 'min:2|max:200',
                'cp'        => 'regex:/^[0-9]+$/|min:5|max:5',
                'city'      => 'min:2|max:100',
            ]);
            if ($validator->fails()) {
                return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
            }
        }
        //Check that we have in stock and that total from client matches new calculated total
        $cart = $request->cart;
        foreach($cart as $item){
            $obj = (object)[];
            $product = Product::find($item['id']);
            if (!$product) {
                return response()->json(['response'=>'error', 'message'=>__('order.error_product')], 400);
            }
            if ($product->stock< $item['quantity']) {
                return response()->json(['response'=>'error', 'message'=>__('order.error_product')], 400);
            }
        }



        $result = (object)[];
        $result->price              = $this->_getCartPrice($cart);
        $result->deliveryCost       = $this->_getDeliveryPrice($cart, $request->delivery);
        $result->weight             = $this->_getCartWeight($cart);
        $result->cart               = $this->_cartToJson($cart);
        $result->total              = $result->price + $result->deliveryCost;
        if ($result->total != $request->total) {
            return response()->json(['response'=>'error', 'message'=>__('order.error_total')], 400);   //!!!!!!!!!! TRANSLATE
        }
        if ($result->total <=0) {
            return response()->json(['response'=>'error', 'message'=>__('order.error_total')], 400);   //!!!!!!!!!! TRANSLATE
        }
        //Create a preOrder
        $order = Order::create([
            'user_id'           => $request->user_id,
            'firstName'         => $request->firstName,
            'lastName'          => $request->lastName,
            'email'             => $request->email,            
            'mobile'            => $request->mobile,
            'delivery'          => $request->delivery,
            'address1'          => $request->address1,
            'address2'          => $request->address2,
            'cp'                => $request->cp,
            'city'              => $request->city,
            'total'             => $result->price + $result->deliveryCost,
            'deliveryCost'      => $result->deliveryCost,
            'price'             => $result->price,
            'cart'              => $result->cart,
            'status'            => 'preorder'
        ]);        
        //Create a payment Intent now
        $total = ($result->price + $result->deliveryCost)*100; //Need to move from cents
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $intent = PaymentIntent::create([
            "amount" => $total,
            "currency" => "eur",
            "payment_method_types" => ["card"],
            "description" => "COMMANDE " . $order->id,
            "metadata" => [
                "Commande" => $order->id,
                "Prénom" => $order->firstName,
                "Nom" => $order->lastName,
                "Livraison" => $order->delivery,
                "Adresse1" => $order->address1,
                "Adresse2" => $order->address2,
                "Ville" => $order->city,
                "Code Postale" => $order->cp,
                "Articles" => $result->cart,
                "Poids total" => $result->weight,
                "Prix" => $order->price,
                "Cout Livraison" => $order->deliveryCost,
                "Total" => $order->total
            ]
        ]);
        $order->payment_id = $intent->id;
        $order->update();
        //Return the transaction key
        return response()->json(['key'=> $intent->client_secret]);
    }

    //Handles the webhook provided by Stripe
    public function webhook(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK'); //Webhook secret key

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        
        try {
          $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
          );
        } catch(\UnexpectedValueException $e) {
          // Invalid payload
          http_response_code(400); // PHP 5.4 or greater
          exit();
        } catch(\Stripe\Error\SignatureVerification $e) {
          // Invalid signature
          http_response_code(400); // PHP 5.4 or greater
          exit();
        }
        
        if ($event->type == "payment_intent.succeeded") {
          $intent = $event->data->object;
          printf("Succeeded: %s", $intent->id);
          $this->setPayedOrder($intent->id);
          http_response_code(200);
          exit();
        } elseif ($event->type == "payment_intent.payment_failed") {
          $intent = $event->data->object;
          $error_message = $intent->last_payment_error ? $intent->last_payment_error->message : "";
          printf("Failed: %s, %s", $intent->id, $error_message);
          $this->setNotPayedOrder($intent->id);  
          http_response_code(200);
          exit();
        }
    }

    //Updates the order as payed and notifies user via email
    function setPayedOrder($payment_id) {
        $order = Order::where('payment_id', $payment_id)->first();//find($intent->metadata->Commande);
        $order->status = "paiement accepté";
        $order->update();
        //Now we need to quickly update the stock !
        $cart = json_decode($order->cart);
        $this->_updateProducts($cart);
        //Now send email to customer confirming the shopping
        $html = "<div>
        <h2>" . __('email.order_title') . "</h2>
        <h3>" . __('email.order_total', ['total'=>$order->total]) . "</h3>
        <h4>" . __('email.order_reference', ['reference'=>$order->id]) . "</h4>
        <h4>" . __('email.order_delivery') . "</h4>";
        if (!$order->delivery) {
            $html = $html . "<p>" . __('email.order_nodelivery') . "</p>";
        } else {
            $html = $html . "<p>" . $order->address1 . "</p>
                            <p>" . $order->address2 . "</p> 
                            <p>" . $order->cp . "</p> 
                            <p>" . $order->city . "</p>
                            <p>FRANCE</p>"; 
        }
        $html = $html . "<h4>" . __('email.order_products') . "</h4>";
        foreach($cart as $item) {
            $product = Product::find($item->id);
            $html = $html . "<p>" . $item->quantity . " x   " . $product->title . "</p>"; 
        }
        $html = $html . "</div>";
        $data = ['html' => $html];
        $this->sendEmail($order->email, __('email.order_subject'), $data);
        $this->sendEmail("cassemoto06@gmail.com", "Nouvelle commande reçue sur le site internet", $data);

    }

    //Updates the order as refused
    function setNotPayedOrder($payment_id) {
        $order = Order::where('payment_id', $payment_id)->first();
        $order->status = "paiement refusé";
        $order->update();
    }
}

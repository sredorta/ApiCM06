<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Configuration;
use Validator;
class ConfigurationController extends Controller
{
    //Return our messages
    public function get(Request $request) {
        return response()->json(Configuration::all()->toArray(),200);
    }

    //Sets the config
    public function set(Request $request) {
        $validator = Validator::make($request->all(), [
            'message_title'   => 'nullable|min:2|max:100',
            'message_text'    => 'nullable|min:2|max:500',
            'delivery1'       => 'required|numeric|min:1|max:1000',
            'delivery2'       => 'required|numeric|min:1|max:1000',
            'delivery3'       => 'required|numeric|min:1|max:1000',
            'address'         => 'required|min:2|max:500',
            'email'           => 'required|email',
            'phone'           => 'required|regex:/^[0-9]+$/|min:10|max:10',
            'latitude'        => 'required|numeric',
            'longitude'       => 'required|numeric',
            'zoom'            => 'required|numeric|min:1|max:30',
            'timetable1'      => 'required|min:2|max:100',
            'timetable2'      => 'nullable|min:2|max:100',
            'timetable3'      => 'nullable|min:2|max:100'  

        ]);
        if ($validator->fails()) {
            return response()->json(['response'=>'error', 'message'=>$validator->errors()->first()], 400);
        }     
        foreach ($request->all() as $key => $value) {
            $this->setItem($key,$value);
        }
        return response()->json(Configuration::all()->toArray(),200);
    }

    private function setItem($item,$value) {
        $result = Configuration::where('key',$item)->first();
        if ($result) {
            $result->value = $value;
            $result->save();
        } else {
            //Create the record
            Configuration::create(["key"=>$item, "value"=>$value]);
        }

    }
}

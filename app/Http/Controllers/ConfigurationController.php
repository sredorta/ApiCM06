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
            'address'         => 'nullable|min:2|max:500',
            'email'           => 'nullable|email',
            'phone'           => 'nullable|regex:/^[0-9]+$/|min:10|max:10',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'zoom'            => 'nullable|numeric|min:1|max:30',
            'timetable1'      => 'nullable|min:2|max:100',
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

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
        return response()->json(["response"=>"success", "message"=> "Il faut faire ca"],200);

    }
}

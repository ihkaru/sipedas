<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function responsejson($code,$data,$message = ''){
        $status = ($code>=200 && $code<300)?true:false;
        if($message == ''){
            return response()->json([
                "status"=>$status,
                "code"=>$code,
                "data"=>$data
            ],$code);
        }else{
            return response()->json([
                "status"=>$status,
                "code"=>$code,
                "data"=>$data,
                "message"=>$message
            ],$code);
        }
    }

    public function responseabort($code,$message){
        return $this->responsejson($code,["message"=>$message]);
    }

    public function emptyHandler(String $key){
        if(request($key)){
            return request($key);
        }else{
            if(request()->exists($key)){
                return "";
            }else {
                return null;
            }
        }
    }
}

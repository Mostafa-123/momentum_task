<?php

namespace App;

if(!function_exists('apiResponse')){
    function apiResponse($status=null,$data=null,$message=null){
        $array=[
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
        return response($array,$status);
    }
}

if(!function_exists('postData')){
    function postData($data){
        $image=$data->image;
        if($image){
            $image="http://127.0.0.1:8000/api/postimage/".$data->id;
        } 
        $data=[
            'id' => $data->id,
            'title' => $data->title,
            'content' => $data->content,
            'image' => $image,
        ];
        return $data;
   }
}

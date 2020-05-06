<?php

require_once "tool.php";

class gQRcode{
    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","data"=>"");
        // $this->create();
        if(!isset($request->get)
            || !isset($request->get["rmid"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }
        $rmid=$request->get["rmid"];

       // $res=\mtool\getQRcode($rmid);
        $res=\mtool\getQRcodeBase64($rmid);
        $successReturn["data"]=$res;
        return json_encode($successReturn);
    }
}
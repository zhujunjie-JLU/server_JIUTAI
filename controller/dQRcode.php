<?php
require_once "tool.php";
//下载二维码
class dQRcode{
    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","path"=>"");
        if(!isset($request->get)
            || !isset($request->get["rmid"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }
        $rmid=$request->get["rmid"];
        $successReturn["path"]=\mtool\getQRcodePath($rmid);
        return json_encode($successReturn);
    }
}
<?php
//获取微信的openid接口,由于微信修改了获取openid的方法
require_once "tool.php";
class goid{
    public function run($request)
    {
        $errorReturn = array("status" => "fail", "errno" => "");
        $successReturn = array("status" => "success","openid"=>"");
        if (!isset($request->get)
            || !isset($request->get["code"])) {
            $errorReturn["errno"] = "参数错误";
            return json_encode($errorReturn);
        }
        $code=$request->get["code"];
        $getOpenID=\mtool\getOpenID($code);
        if($getOpenID["status"]=="fail"){
            return json_encode($getOpenID);
        }

        $openid=$getOpenID["openid"];
        $successReturn["openid"]=$openid;
        return json_encode($successReturn);
    }
}

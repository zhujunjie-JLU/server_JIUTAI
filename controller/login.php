<?php
require_once "tool.php";
//登录接口
class login
{
    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success",
            "rmname"=>'',"pname"=>"",
            "pr"=>"0",
            "rmid"=>"", "ban"=>"n");
        if(!isset($request->get)
        || !isset($request->get["openid"])
        || !isset($request->get["rmid"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }


        $openid=$request->get["openid"];
        $rmid=$request->get["rmid"];

        $db=new \mtool\mysql();
        $res=$db->select("peopleTable",[],["openid"=>$openid,"available"=>"1"]);
        //找不到对应的人员
        if(count($res)==0){
            return json_encode($successReturn);
        }

        if($rmid==""||$rmid=="undefined") {
            if ($res[0]["pr"] == '1') {
                //巡检不扫描登录
                $errorReturn["errno"]="请扫描对应的小程序码进入小程序";
                return json_encode($errorReturn);//返回空无权限
            }
        }elseif($rmid!=$res[0]["rmid"]){
            $errorReturn["errno"]="您无本机房的权限,请扫描正确的小程序码";
            return json_encode($errorReturn);//如果传入rmid则必须是匹配的rmid

        }


        $rmid=$res[0]["rmid"];
        $successReturn["rmid"]=$rmid;
        $successReturn["pname"]=$res[0]["pname"];

        //查找机房
        $successReturn["pr"]=$res[0]["pr"];
        $res=$db->select("rmTable",[],["rmid"=>$rmid]);
        if(count($res)==0){
            $errorReturn["errno"]="机房错误";
            return json_encode($errorReturn);
        }
        //如果机房已经被删除,则删除机房中所有的用户信息
        if($res[0]["available"]=='0'){
            $res=$db->update("peopleTable",["available"=>'0'],["rmid"=>$rmid]);
            $res=$db->delete("tempPrTable",["rmid"=>$rmid]);
            $successReturn["rmid"]="";
            $successReturn["pname"]="";
            $successReturn["pr"]="0";
            return json_encode($successReturn);
        }
        $successReturn["rmname"]=$res[0]["rmname"];
        $successReturn["ban"]=$res[0]["ban"];



        return json_encode($successReturn);
    }


}

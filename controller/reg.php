<?php
//微信小程序用户绑定权限
require_once "tool.php";



class reg{
    public function run($request)
    {
        $errorReturn = array("status" => "fail", "errno" => "");
        $successReturn = array("status" => "success");

        if (!isset($request->get)
            || !isset($request->get["openid"])
            || !isset($request->get["inv"])) {
            $errorReturn["errno"] = "error parameter";
            return json_encode($errorReturn);
        }


        $openid=$request->get["openid"];
        $inv=$request->get["inv"];

	if($openid==""){
		$errorReturn["errno"]="openid错误";
		return $errorReturn;
	}

        $db=new \mtool\mysql();
        //获取邀请码
        $res= $db->select("tempPrTable",[],["inv"=>$inv]);
        if(count($res)==0){
            $errorReturn["errno"]="邀请码错误,请联系管理员";
            return json_encode($errorReturn);
        }
        $peoplename=$res[0]["pname"];
        $rmid=$res[0]["rmid"];
        $pr=$res[0]["pr"];
        $pid=$res[0]["pid"];

        $res=$db->select("rmTable",[],["rmid"=>$rmid]);
        if(count($res)==0){
            $errorReturn["errno"]="邀请码错误,请联系管理员";
            return json_encode($errorReturn);
        }

        $db->delete("tempPrTable",["inv"=>$inv]);
        $res=$db->insert("peopleTable",["openid"=>$openid,"pname"=>$peoplename,"pr"=>$pr,"rmid"=>$rmid,"available"=>'1',"pid"=>$pid]);
        if($res["affected_rows"]===0){
            return json_encode($errorReturn);
        }

        return json_encode($successReturn);
    }
}

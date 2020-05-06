<?php

require_once "tool.php";

class uplog
{
    public function run($request){

        ini_set('date.timezone','Asia/Shanghai');
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success");
        if(!isset($request->get)
            || !isset($request->get["openid"])
            || !isset($request->get["rmid"])
            || !isset($request->get["temperature"])
            || !isset($request->get["humidity"])
            || !isset($request->get["devicestatus"])
            || !isset($request->get["ups"])
            || !isset($request->get["analysis"])
            || !isset($request->get["remarks"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }

        $openid=$request->get["openid"];
        $rmid=$request->get["rmid"];
        $temperature=$request->get["temperature"];
        $humidity=$request->get["humidity"];
        $devicestatus=$request->get["devicestatus"]=='1'?'正常':'异常';
        $ups=$request->get["ups"];
        $analysis=$request->get["analysis"];
        $remarks=$request->get["remarks"];


        $date=date('Y-m-d');

        $db=new \mtool\mysql();

        $res=$db->select("peopleTable",[],["openid"=>$openid,"available"=>'1']);
        if(count($res)==0){
            $errorReturn["errno"]="人员错误";
            return json_encode($errorReturn);
        }
        $pid=$res[0]["pid"];
        $pname=$res[0]["pname"];

	//负责人用户在巡检时有可能不上传rmid,则赋值
	if($res[0]["pr"]==2&&$rmid==""){
		$rmid=$res[0]["rmid"];
	}


        //错误的房间
        if($rmid!=$res[0]["rmid"]){
            $errorReturn["errno"]="房间错误";
            return json_encode($errorReturn);
        }


        $res=$db->delete("notesTable",["date"=>$date,"rmid"=>$rmid]);
        //直接删除之前的巡检记录


        $res=$db->insert("notesTable",[
            "pid"=>$pid,
            "rmid"=>$rmid,
            "temperature"=>$temperature,
            "humidity"=>$humidity,
            "deviceStatus"=>$devicestatus,
            "ups"=>$ups,
            "analysis"=>$analysis,
            "remarks"=>$remarks,
            "date"=>$date,
            "pname"=>$pname,
            "time"=>date('Y-m-d H:i:s'),
            "available"=>"1",
        ]);
        if($res===false){
            $errorReturn["errno"]="insert error";
            return json_encode($errorReturn);
        }

        return json_encode($successReturn);

    }
}

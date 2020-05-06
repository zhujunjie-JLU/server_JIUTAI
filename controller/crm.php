<?php
//创建机房接口
require_once "tool.php";

class crm{
    public function run($request){
        ini_set('date.timezone','Asia/Shanghai');
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","rmid"=>"");

        if(!isset($request->get)
        || !isset($request->get["rmname"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }

        $rmname=$request->get["rmname"];
        $db=new \mtool\mysql();
        $guid=\mtool\guid();
        $res=$db->insert("rmTable",["rmid"=>$guid,"rmname"=>$rmname,"ban"=>"n","available"=>"1"]);
        if($res["affected_rows"]!=1){
            $errorReturn["errno"]="insert error";
            return json_encode($errorReturn);
        }

        //获得二维码并下载
        $res=\mtool\getQRcode($guid);
        \mtool\saveQRcode($guid,$res);


        //新建的机房立即插入一条未巡检记录
        $date = date('Y-m-d');
        $rr= $db->insert("notesTable", [
            "pid" => "",
            "rmid" => $guid,
            "temperature" => "",
            "humidity" => "",
            "deviceStatus" => "",
            "ups" => "",
            "analysis" => "",
            "remarks" => "",
            "date" => $date,
            "pname" => "",
            "time" => date('Y-m-d H:i:s'),
            "available" => "0",
        ]);
	

	$successReturn["rmid"]=$guid;
        return json_encode($successReturn);
    }
}

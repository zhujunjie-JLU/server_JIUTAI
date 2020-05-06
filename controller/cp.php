<?php

require_once "tool.php";

class cp{
    public function run($request){
        $errorReturn = array("status" => "fail", "errno" => "");
        $successReturn = array("status" => "success", "inv"=>"");
        if (!isset($request->get)
            || !isset($request->get["rmid"])
            || !isset($request->get["pname"])
            || !isset($request->get["pr"])) {
            $errorReturn["errno"] = "参数错误";
            return json_encode($errorReturn);
        }

        $pname=$request->get["pname"];
        $rmid=$request->get["rmid"];
        $pr=$request->get["pr"];
        $pid=\mtool\guid();

        if($pname==''){
            $errorReturn["errno"]="empty name";
            return json_encode($errorReturn);
        }


        $db=new \mtool\mysql();
        $res=$db->select("rmTable",[],["rmid"=>$rmid]);
        if(count($res)==0){
            $errorReturn["errno"]="error room";
            return json_encode($errorReturn);
        }

        $inv=\mtool\inv();
        $res=$db->insert("tempPrTable",["inv"=>$inv,"rmid"=>$rmid,"pr"=>$pr,"pname"=>$pname,"time"=>time(),"pid"=>$pid]);
        if($res["affected_rows"]===0){
            return json_encode($errorReturn);
        }
        $successReturn["inv"]=$inv;
        return json_encode($successReturn);

    }
}

<?php

require_once "tool.php";

class brm{
    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","ban"=>"n");
        if(!isset($request->get)
            || !isset($request->get["rmid"])) {
            $errorReturn["errno"] = "参数错误";
            return json_encode($errorReturn);
        }
        $rmid=$request->get["rmid"];
        $db=new \mtool\mysql();
        $res=$db->select("rmTable",["ban"],["rmid"=>$rmid]);
        if(count($res)===0){
            $errorReturn["errno"]="错误的机房";
            return json_encode($errorReturn);
        }

        $ban=$res[0]["ban"]=='n'?'y':'n';
        $res=$db->update("rmTable",["ban"=> $ban],["rmid"=>$rmid]);
        if($res["affected_rows"]===0){
            $errorReturn["errno"]="错误的房间";
            return json_encode($errorReturn);
        }

        $successReturn["ban"]=$ban;

        return json_encode($successReturn);
    }
}


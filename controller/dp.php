<?php

require_once "tool.php";

class dp{
    public function run($request)
    {
        $errorReturn = array("status" => "fail", "errno" => "");
        $successReturn = array("status" => "success");
        if (!isset($request->get)
            || !isset($request->get["pid"])) {
            $errorReturn["errno"] = "参数错误";
            return json_encode($errorReturn);
        }

        $pid=$request->get["pid"];
        $db=new \mtool\mysql();

        $res=$db->update("peopleTable",["available"=>'0'],["pid"=>$pid]);
        if($res["affected_rows"]===0){
            $res=$db->select("tempPrTable",[],["pid"=>$pid]);
            if(count($res)==0) {
                $errorReturn["errno"] = "错误的人员";
                return json_encode($errorReturn);
            }
            $res=$db->delete("tempPrTable",["pid"=>$pid]);
        }

        return json_encode($successReturn);
    }
}

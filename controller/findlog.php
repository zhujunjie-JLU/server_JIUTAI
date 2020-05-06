<?php
require_once "tool.php";

class findlog{

    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","log"=>[]);

        if(!isset($request->get)
            || !isset($request->get["start"])
            || !isset($request->get["end"])
            || !isset($request->get["rmid"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }

        $start=$request->get["start"];
        $end=$request->get["end"];
        //初始值
        if($start=="000000"&&$end=="000000"){
            $start='000101';
            $end='991231';
        }

        $start="20".$start;
        $end="20".$end;

        $db=new \mtool\mysql();
        $rmid=$request->get["rmid"];

        $res=$db->selectNote($rmid,$start,$end);

        $successReturn["log"]=$res;
        return json_encode($successReturn);
    }
}

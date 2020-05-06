<?php

require_once "tool.php";
//添加人员接口
class addp{
    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","p"=>[]);

        if(!isset($request->get)
            || !isset($request->get["rmid"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }
        $rmid=$request->get["rmid"];

        $db=new \mtool\mysql();
        $res = $db->selectNote($rmid,"20150909","20191111");
        $successReturn["p"]=$res;
        return json_encode($successReturn);

    }

}

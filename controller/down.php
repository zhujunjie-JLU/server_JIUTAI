<?php

//文件下载的处理

require_once "tool.php";



class down{

    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","path"=>"");

        $fileName="";
        $title=[
            "巡检时间",
            "巡检日期",
            "机房温度",
            "机房湿度",
            "设备状态",
            "UPS负载量",
            "分析处理",
            "巡检人",
            "备注",
        ];

        // $this->create();
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


        $rmid=$request->get["rmid"];
        $db=new \mtool\mysql();
        $res=$db->select("rmTable",[],["rmid"=>$rmid,"available"=>'1']);
        if(count($res)==0){
            $errorReturn["errno"]="error room";
            return json_encode($errorReturn);
        }
        $rmname=$res[0]["rmname"];


        $ret=$db->selectNote($rmid,$start,$end);


        for($i=0;$i<count($ret);$i++){
            if($ret[$i]["available"]!='1'){
                $ret[$i]["remarks"]="未巡检";
                $ret[$i]["time"]="";
            }
            unset($ret[$i]["available"]);
        }
        //var_dump($ret);

//        $p=\mtool\exportExcel($rmname,$title,$ret,$rmid);
        $p=\mtool\exportExcel($rmname,$title,$ret,$rmname);

	    $successReturn["path"]=$p;
        return json_encode($successReturn);

    }
}

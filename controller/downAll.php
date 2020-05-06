<?php

require_once "tool.php";



class downAll{

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
            || !isset($request->get["end"])){
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


        $excelData=[];

       // $rmid=$request->get["rmid"];
        $db=new \mtool\mysql();
        $rm=$db->select('rmTable',[],['available'=>'1']);
        for($i=0;$i<count($rm);$i++){
            $rmid=$rm[$i]["rmid"];
            $rmname=$rm[$i]["rmname"];

            $note=$db->selectNote($rmid,$start,$end);
            for($j=0;$j<count($note);$j++){
                if($note[$j]["available"]!='1'){
                    $note[$j]["remarks"]="未巡检";
                    $note[$j]["time"]="";
                }
                unset($note[$j]["available"]);
            }
            $p=["name"=>$rmname,"data"=>$note,"title"=>$title];
            $excelData[]=$p;
        }
        $ret=\mtool\excelTest("机房巡检记录汇总表",$excelData,"机房巡检记录汇总表");
        $successReturn["path"]=$ret;
        return json_encode($successReturn);

    }
}

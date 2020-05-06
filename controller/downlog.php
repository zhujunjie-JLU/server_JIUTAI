<?php

require_once "tool.php";

class downlog{
    public function run($request){
        $errorReturn=array("status"=>"fail","errno"=>"");
        $successReturn=array("status"=>"success","path"=>"");

        $title=[
            '机房名称',
            '未巡检日期',
        ];

        if(!isset($request->get)
            || !isset($request->get["start"])
            || !isset($request->get["end"])){
            $errorReturn["errno"]="参数错误";
            return json_encode($errorReturn);
        }
        $start=$request->get["start"];
        $end=$request->get["end"];
        if($start=="000000"&&$end=="000000"){
            $start='000101';
            $end='991231';
        }
        $start="20".$start;
        $end="20".$end;


        //$fileName="机房未巡检统计表 统计时间";

        $db=new \mtool\mysql();
       // $sql="SELECT  rmTable.rmname as rmname ,date from notesTable join rmTable on notesTable.available=0 AND notesTable.rmid=rmTable.rmid WHERE notesTable.date>=".$start." AND notesTable.date<= ".$end." ORDER BY rmname , date ;";
        $sql="SELECT rmTable.rmname,GROUP_CONCAT(notesTable.date SEPARATOR ' ') AS date FROM notesTable JOIN rmTable on notesTable.available=0 AND notesTable.rmid=rmTable.rmid WHERE notesTable.date>=".$start." AND notesTable.date<=".$end." GROUP BY rmTable.rmname;";
        $ret=$db->query($sql);


        $p=\mtool\exportExcel("机房未巡检统计表",$title,$ret,"机房未巡检记录表");
        $successReturn["path"]=$p;
        return json_encode($successReturn);
    }
}

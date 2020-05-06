<?php
//在数据库中插入今日的空巡检
require_once "tool.php";

class ue{
    public function run($request){

        ini_set('date.timezone','Asia/Shanghai');
        $db=new \mtool\mysql();
        $res=$db->select("rmTable",[],["available"=>1]);

        foreach ($res as $r) {

            $date = date('Y-m-d');

            $rr= $db->insert("notesTable", [
                "pid" => "",
                "rmid" => $r["rmid"],
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
            if ($rr == false) {
                echo "insert error";
            }

        }

        \mtool\clearExcel();
        return "";
    }
}

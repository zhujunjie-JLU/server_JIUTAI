<?php
//获取机房的列表
require_once "tool.php";
class grm
{
    public function run($request){
        $successReturn=array("status"=>"success","rm"=>[]);
        $db=new \mtool\mysql();
        $res=$db->select("rmTable",["rmid","rmname","ban"],["available"=>"1"]);
        for($i=0;$i<count($res);$i++ ){
            $r=$db->select("peopleTable",["pname","pr","pid"],["rmid"=>$res[$i]["rmid"],"available"=>"1"]);
            //$res[$i]["p"]=$r;

            $rr=$db->select("tempPrTable",["pname","inv","pr","pid"],["rmid"=>$res[$i]["rmid"]]);
            if($rr!=false) {
                foreach ($rr as $x) {
                    $r[] = [
                        "pname" => $x["pname"] . " 邀请码:" . $x["inv"],
                        "pr" => $x["pr"],
                        "pid" => $x["pid"],
                    ];
                }
            }

            $res[$i]["p"]=$r;
        }
        $successReturn["rm"]=$res;
        return json_encode($successReturn);
    }
}
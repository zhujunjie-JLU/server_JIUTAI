<?php
require_once "tool.php";

class gp
{
    public function run($request)
    {
        $errorReturn = array("status" => "fail", "errno" => "");
        $successReturn = array("status" => "success", "people" => []);
        if (!isset($request->get)
            || !isset($request->get["rmid"])) {
            $errorReturn["errno"] = "参数错误";
            return json_encode($errorReturn);
        }

        $rmid=$request->get["rmid"];

        $db=new \mtool\mysql();
        $res=$db->select("peopleTable",["pname","pr","pid"],["rmid"=>$rmid,"available"=>"1"]);

        $rr=$db->select("tempPrTable",["pname","inv","pr","pid"],["rmid"=>$rmid]);
        if($rr!=false) {
            foreach ($rr as $x) {
                $res[] = [
                    "pname" => $x["pname"] . " 邀请码:" . $x["inv"],
                    "pr" => $x["pr"],
                    "pid" => $x["pid"],
                ];
            }
        }
        $successReturn["people"]=$res;
        return json_encode($successReturn);
    }
}

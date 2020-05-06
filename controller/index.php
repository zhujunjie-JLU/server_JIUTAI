<?php
//错误页
class Index
{
    public function home($request)
    {
        return json_encode(array(
            "status"=>"fail",
            "errno"=>"url error",
        ));
    }
}
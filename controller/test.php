<?php

require_once "tool.php";

class test{

    public function run($request){
	\mtool\clearExcel();

	return "success";
    }
}

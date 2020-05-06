<?php
namespace mtool;

use mysql_xdevapi\Warning;
require_once "par.php";

//生成guid函数

function getOpenID($code){
    $errorReturn= array("status"=>"fail","errno"=>"");
    $successReturn =array("status"=>"success","openid"=>"");
    $url="https://api.weixin.qq.com/sns/jscode2session?appid="
        .APPID."&secret=".SECRET."&js_code=".$code."&grant_type=authorization_code";
    $res=file_get_contents($url);
    $js=json_decode($res,true);

    if(isset($js["errcode"])){
        $errorReturn["errno"]=json_encode($res);
        return $errorReturn;
    }

    $successReturn["openid"]=$js["openid"];

    return $successReturn;
}

function guid(){
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
    }
}

//生成邀请码函数邀请码是6位数字
function inv(){
        return sprintf('%d%d%d%d%d%d', rand(0, 9),rand(0, 9),rand(0, 9),rand(0, 9),rand(0, 9),rand(0, 9));
}

//mysql的链接类
class mysql{

    public $host=MYSQL_HOST;
    private $port=MYSQL_PORT;
    private $user=MYSQL_USER;
    private $pass=MYSQL_PASS;
    private $dbname=MYSQL_DBNAME;

    public function db_connect(){
        $db=new \Swoole\Coroutine\Mysql();
        $db->connect([
            "host"=>$this->host,
            "port"=>$this->port,
            "user"=>$this->user,
            "password"=>$this->pass,
            "database"=>$this->dbname,
        ]);
        //$conn=new mysqli($this->host,$this->user,$this->pass,$this->dbname);
        return $db;
    }

    //为了查询记录而特例的函数
    public function selectNote($rmid,$start,$end){
        $sql="SELECT time,date,temperature,humidity,devicestatus as deviceStatus,ups,pname,analysis,remarks,available FROM notesTable WHERE date>=".$start." AND date <= ".$end." AND rmid = '{$rmid}' ORDER BY date";
        return $this->_execute($sql);
    }

    public function query($sql=''){
        return $this->_execute($sql);
    }

    public function insert($table = '', $data = [])
    {
        $fields = '';
        $values = '';
        $keys = array_keys($data);
        foreach ($keys as $k) {
            $fields .= "`".addslashes($k)."`, ";
            $values .= "'".addslashes($data[$k])."', ";
        }
        $fields = substr($fields, 0, -2);
        $values = substr($values, 0, -2);
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$values})";
        return $this->_execute($sql);
    }

    public function select($table='',$show=[],$where=[]){
        $where=$this->_where($where);
        $show=$this->_show($show);
        $sql="SELECT ".$show." FROM `{$table}` {$where}";
        return $this->_execute($sql);
    }


    public function delete($table = '', $where = [])
    {
        $where = $this->_where($where);
        $sql = "DELETE FROM `{$table}` {$where}";
        return $this->_execute($sql);
    }

    public function update($table = '', $set = [], $where = [])
    {
        $arr_set = [];
        foreach ($set as $k => $v) {
            $arr_set[] = '`'.$k . '` = ' . $this->_escape($v);
        }
        $set = implode(', ', $arr_set);
        $where = $this->_where($where);
        $sql = "UPDATE `{$table}` SET {$set} {$where}";
        return $this->_execute($sql);
    }

    private function _where($where = [])
    {
        $str_where = '';
        foreach ($where as $k => $v) {
            $str_where .= " AND `{$k}` = ".$this->_escape($v);
        }
        return "WHERE 1 ".$str_where;
    }

    private function _show($show=[]){
        $str_show='';
        if(count($show)==0){
            return '*';
        }

        foreach ($show as $v){
            $str_show.=$v.',';
        }

        $str_show=rtrim($str_show,',');
        return $str_show;
    }

    public function _escape($str){
        if(is_string($str)){
            $str="'".$str."'";
        }elseif(is_bool($str)){
            $str=($str===FALSE)?0:1;
        }elseif(is_null($str)){
            $str='NULL';
        }
        return $str;
    }

    public function _execute($sql){
        echo $sql.PHP_EOL;
        $conn=$this->db_connect();
        $result=$conn->query($sql);
        if ($result === true) {
            $conn->close();
            return [
                'affected_rows' => $conn->affected_rows,
                'insert_id'     => $conn->insert_id,
            ];
        }
        $conn->close();
        return $result;
    }
}

function http_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
  //  curl_setopt($curl,CURLOPT_HTTPHEADER,array(
   //         "responseTypy"=>"arraybuffer",
   // ));

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

function getQRcodePath($rmid){
    return EXCELBASEPATH.$rmid.".jpg";
}

function saveQRcode($name,$data){
    $filePath=JPGSAVEPATH.$name.".jpg";
    $file=fopen($filePath,"w");
    fwrite($file,$data);
    fclose($file);
}

//二维码获取函数
function getQRcode($parm){
    $errorReturn= array("status"=>"fail","errno"=>"");
    $successReturn =array("status"=>"success","date"=>"");

    $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid="
        .APPID."&secret=".SECRET;
    $res=file_get_contents($url);
    $js=json_decode($res,true);
    if(isset($js["access_token"])){
        $errorReturn["errno"]=json_encode($res);
    }
   // var_dump($res);
    var_dump($url);
    var_dump($js);
    $access_token=$js["access_token"];

    $path='pages/index/index?name='.$parm;


    $url="https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token;
    $postData=json_encode([
        "path"=>$path,
    ]);

    $res=http_request($url,$postData);
    return $res;

}

function getQRcodeBase64($rmid){
    $path=JPGSAVEPATH.$rmid.".jpg";
    $file=fopen($path,"r");
    $res=fread($file,filesize($path));
    fclose($file);
    return base64_encode($res);
}

//excel 文件生成函数
function excelTest($top="",$pac=array(), $fileName=''){

//    include('PHPExcel.php');
    include_once "PHPExcel.php";
    $obj = new \PHPExcel();

    //横向单元格标识
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');


    $sheetIndex=0;
    foreach($pac AS $p) {
        $sheetName = $p['name'];
        $title = $p['title'];
        $data = $p['data'];
        if($sheetIndex>0){
            $obj->createSheet($sheetIndex);
        }
        $obj->setActiveSheetIndex($sheetIndex);
        $obj->getActiveSheet()->setTitle($sheetName);
      //  $obj->getActiveSheet($sheetIndex)->setTitle($sheetName);

        //$obj->getActiveSheet(0)->setTitle('sheet名称');   //设置sheet名称
        $_row = 1;   //设置纵向单元格标识
        if ($title) {
            $_cnt = count($title);
            $obj->getActiveSheet()->mergeCells('A' . $_row . ':' . $cellName[$_cnt - 1] . $_row);   //合并单元格
            $obj->getActiveSheet()->setCellValue('A' . $_row, $sheetName . '日常机房巡检记录表');
            // $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出：'.date('Y-m-d H:i:s'));  //设置合并后的单元格内容
            $_row++;
            $i = 0;
            foreach ($title AS $v) {   //设置列标题
                $obj->getActiveSheet()->setCellValue($cellName[$i] . $_row, $v);
                $i++;
            }
            $_row++;
        }

        //填写数据
        if ($data) {
            $i = 0;
            foreach ($data AS $_v) {

                $j = 0;
                foreach ($_v AS $_cell) {
                    $obj->getActiveSheet()->setCellValue($cellName[$j] . ($i + $_row), $_cell);
                    $j++;
                }
                $i++;
            }
        }
        $sheetIndex++;
    }

    $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel5');

    //$_fileName = iconv("utf-8", "gb2312", $fileName);   //转码
    $tname=guid();
    $_fileName = iconv("utf-8", "gb2312", $tname);   //转码

    $_savePath = FILESAVEPATH.$_fileName.'.xls';
    $objWrite->save($_savePath);


    rename($_savePath,FILESAVEPATH.$fileName.'.xls');

    return EXCELBASEPATH.$fileName.'.xls';

}

//excel 文件生成函数
function exportExcel($top="",$title=array(), $data=array(), $fileName=''){

//    include('PHPExcel.php');
	include_once "PHPExcel.php";
    $obj = new \PHPExcel();

    //横向单元格标识
    $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

    //$obj->getActiveSheet(0)->setTitle('sheet名称');   //设置sheet名称
    $_row = 1;   //设置纵向单元格标识
    if($title){
        $_cnt = count($title);
        $obj->getActiveSheet(0)->mergeCells('A'.$_row.':'.$cellName[$_cnt-1].$_row);   //合并单元格
        $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row,$top.'日常机房巡检记录表');
       // $obj->setActiveSheetIndex(0)->setCellValue('A'.$_row, '数据导出：'.date('Y-m-d H:i:s'));  //设置合并后的单元格内容
        $_row++;
        $i = 0;
        foreach($title AS $v){   //设置列标题
            $obj->setActiveSheetIndex(0)->setCellValue($cellName[$i].$_row, $v);
            $i++;
        }
        $_row++;
    }

    //填写数据
    if($data){
        $i = 0;
        foreach($data AS $_v){

            $j = 0;
            foreach($_v AS $_cell){
                $obj->getActiveSheet(0)->setCellValue($cellName[$j] . ($i+$_row), $_cell);
                $j++;
            }
            $i++;
        }
    }

    $objWrite = \PHPExcel_IOFactory::createWriter($obj, 'Excel5');

    //$_fileName = iconv("utf-8", "gb2312", $fileName);   //转码
    $tname=guid();
    $_fileName = iconv("utf-8", "gb2312", $tname);   //转码

    $_savePath = FILESAVEPATH.$_fileName.'.xls';
    $objWrite->save($_savePath);
	

	rename($_savePath,FILESAVEPATH.$fileName.'.xls');

    return EXCELBASEPATH.$fileName.'.xls';

}
//每日清理所有的xlsx文件
function clearExcel(){
	array_map('unlink',glob(FILESAVEPATH.'*.xls'));
}

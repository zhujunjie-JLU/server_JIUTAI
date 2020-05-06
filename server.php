<?php
class Server
{
    private $serv;

    public function __construct() {
        $this->serv = new swoole_http_server("0.0.0.0", 9501);
        $this->serv->set([
            'enable_coroutine' => true,
            'task_enable_coroutine' => true,
            ]);
     /*   $this->serv->set(array(
            'worker_num'=> 2, //开启2个worker进程
            'max_request'     => 4, //每个worker进程 max_request设置为4次
            'document_root'   => '',
            'enable_static_handler' => true,
            'daemonize'       => false, //守护进程(true/false)
        ));
*/
        $this->serv->set(array(
            'worker_num' => 2,
            'daemonize' => false,
        ));
        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('WorkerStart', [$this, 'onWorkStart']);
        $this->serv->on('ManagerStart', [$this, 'onManagerStart']);
        $this->serv->on("Request", [$this, 'onRequest']);

        $this->serv->start();
    }

    public function onStart($serv) {
        echo "#### onStart ####".PHP_EOL;
        swoole_set_process_name('swoole_process_server_master');

        echo "SWOOLE ".SWOOLE_VERSION . " 服务已启动".PHP_EOL;
        echo "master_pid: {$serv->master_pid}".PHP_EOL;
        echo "manager_pid: {$serv->manager_pid}".PHP_EOL;
        echo "########".PHP_EOL.PHP_EOL;
    }

    public function onManagerStart($serv) {
        echo "#### onManagerStart ####".PHP_EOL.PHP_EOL;
        swoole_set_process_name('swoole_process_server_manager');
    }

    public function onWorkStart($serv, $worker_id) {
        echo "#### onWorkStart ####".PHP_EOL.PHP_EOL;
        swoole_set_process_name('swoole_process_server_worker');

        spl_autoload_register(function ($className) {
            $classPath = __DIR__ . "/controller/" . $className . ".php";
            if (is_file($classPath)) {
                require "{$classPath}";
                return;
            }
        });

       // date_default_timezone_set(timezone_identifier:'Asia/Shanghai');
    }

    public function onRequest($request, $response) {
        $response->header("Server", "SwooleServer");
        $response->header("Content-Type", "text/html; charset=utf-8");
        $server = $request->server;
        $path_info    = $server['path_info'];
        $request_uri  = $server['request_uri'];

        if ($path_info == '/favicon.ico' || $request_uri == '/favicon.ico') {
            return $response->end();
        }

        $controller = 'index';

        $error_return = array(
            "status"=>"fail",
            "errno"=>"url error",
        );

        if ($path_info != '/') {
            $path_info = explode('/',$path_info);

            $count_path_info = count($path_info);
            if ($count_path_info !=2) {
                return $response->end(json_encode($error_return));

            }
            $controller = (isset($path_info[1]) && !empty($path_info[1])) ? $path_info[1] : $controller;
        }else{
            return $response->end(json_encode($error_return));
        }

        $method="run";
        $result=json_encode(($error_return));
	    var_dump($request);

        if (class_exists($controller)) {
            $class = new $controller();
            if (method_exists($controller, $method)) {
                $result = $class->$method($request);
            }
        }

        return $response->end($result);
    }
}

$server = new Server();


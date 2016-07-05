<?php

return [
    'daemonize' => 0, //服务进程转为守护进程
    'reactor_num' => 1,
    'task_ipc_mode' => 2,
    'worker_num' => 1,    //工作任务的进程数量
    'task_worker_num' => 100,//服务最大的任务数量
    'backlog' => 300,   //等待连接的队列长度
    'max_request' => 0, //工作经常工作多少次后自动重启,0表示不重启
    'dispatch_mode' => 3,
    'max_conn' => 300,
    'server_ip' => '127.0.0.1',
    'server_port' => 9511,
    'package_eof' => "\r\n\r\n",  //http协议就是以\r\n\r\n作为结束符的，这里也可以使用二进制内容
    'open_eof_check' => 1,
    'queue'=>[
        'name'=>'default',
        'ip'=>'127.0.0.1',
        'port'=>'11300',
    ],
    //'open_tcp_keepalive'=>1,
    'heartbeat_check_interval' => 5,
    'heartbeat_idle_time' => 10,
    //'log_path' => '',//指定运行日记的根目录,不配置不写日记
];
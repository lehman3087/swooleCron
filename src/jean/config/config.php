<?php

return [
    'daemonize' => 0, //服务进程转为守护进程
    'reactor_num' => 1,
    'task_ipc_mode' => 2,
    'worker_num' => 2,    //工作任务的进程数量
    'task_worker_num' => 100,//服务最大的任务数量
    'backlog' => 30,   //等待连接的队列长度
    'max_request' => 0, //工作经常工作多少次后自动重启,0表示不重启
    'dispatch_mode' => 3,
    'max_conn' => 3000,
    'server_ip' => '0.0.0.0',
    'server_port' => 9501,
    'task_source' => 'default',//rest接口指定或默认的任务配置文件
    /**
     * 数组格式为：
     * [
            'code'=>0,//0表示成功返回任务
            'data'=>[
                [
                    'taskId'=>1,
                ],
                [
                    'taskId'=>2,
                ],
            ],//任务的二维数
     * ]
     * JSON数据返回
     */
    'task_url' => 'http://api.market.chexiu.dev/v1/member/test',//指定任务接口（rest的get接口,返回指定数组格式的JSON）
    'task_update_interval' => 5,//任务源更新频率,默认30分钟更新一次,系统的client和server都提供即时更新任务的接口
    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
    ],
    'package_eof' => "\r\n\r\n",  //http协议就是以\r\n\r\n作为结束符的，这里也可以使用二进制内容
    'open_eof_check' => 1,

    //'open_tcp_keepalive'=>1,
    'heartbeat_check_interval' => 5,
    'heartbeat_idle_time' => 10,
    //'log_path' => '',//指定运行日记的根目录,不配置不写日记
    'table_key' => md5("SwooleServer__init_20160314"),//REDIS共享KEY
];
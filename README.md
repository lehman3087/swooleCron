<center><h1>jean/swoolecron文档</h1></center>
<li><b>简介：</b></li>
>开发基于swoole的server和process.主要目标：<p>
<ol>
<li>实现多个独立的方法同步并行调用;(已完成)
<li>启动SERVER自行加载以及周期性更新任务配置,并按配置执行相关任务(已完成).
<li>WEB环境下可以立即执行一个异步任务,也可以获取一个队列,将一个异步任务投递到队列当中由队列去控制执行(异步队列任务开发中).
<li>内置HTTP服务,简易WEB管理SERVER服务本身,同时管理SERVER中的任务,队列(开发中).
</ol>
##安装
1. 使用 [composer](https://getcomposer.org/)

  ```shell
  composer require jean/swoolecron
  ```
##使用
1.多进程并行调用独立方法：
-
>使用场景:一个WEB请求过来,通常按顺序可能要调用A,B,C,D等多个方法,而且常常几个方法可能是独立的可以并行执行的,但是PHP的同步堵塞导致只能一个一个方法去调用,影响响应速度.

```php

<?php

use jean\swoolecron\Job;

$rs = Job::syncRun([

            'worker_num' => 3,//任务进程数,最大10,最小1
            'fun' => [//每个进程对应的回调函数,可以只指定一个函数,这样就所有任务进程都调用同一个函数
                array($this, 'test1'),//任务进程1回调函数
                array($this, 'test2'),//任务进程2回调函数
                array($this, 'test3'),//任务进程3回调函数
            ],
            'data' => //可选参数,对应任务进程回调函数的参数
			[
				['任务进程1传递的参数'],
				['任务进程2传递的参数'],
				['任务进程3传递的参数'],
			],
        ]);
执行结果值返回格式(注：回调函数返回结果必须为真,否则会出现异常)：

$rs =[

	0=>$data1,//任务进程1回调函数返回结果
	1=>1$data2,//任务进程2回调函数返回结果
	2=>$data3,//任务进程3回调函数返回结果
]

2.Server和Client的解说和使用:
-
<li>Server启动默认配置(必须配置REDIS)</li>
> 见包目录下 src/cong/config.php

<li>Server启动加载的任务默认配置</li>
> 见包目录下 src/cong/task.php

>单个任务格式：

>[
>
      'taskId'=>'11',//任务ID,全局唯一
      'description'=>'',//任务描述
      'taskType'=>'loop',//任务类型（single,loop）
      'callbackType'=>'script',//回调类型(rest,script)口回调
      'script'=>'/var/www/project/market/yii swoole-server/test',//回调的脚本命令或URL
      'data'=>[],//回调的数据
      'requestType'=>'get',//请求的类型（rest脚本专属）get,post,put,delete
      'on'=>'',//任务首次执行时间戳（秒）,当前时间大于或等于该时间戳时即刻执行,否则等待到该时间戳间执行时间戳间执行
      'interval'=>'1',//同任务执行最短间隔时间,单位秒
      'loop_end'=>'0',//循环任务截止时间戳,默认不限制
      'loop_count'=>'100',//任务循环多少次截止,默认不限制
      'action'=>'reload',//任务指令:执行任务：start;停止任务（如果在执行的化）stop;重载任务:reload
      'actionUpdateTime' =>'2016-01-21 20:07:59' //判断任务是否需要更新,转换时间戳增加表示任务需要更新
],

3.Client使用
-
> 目前只实现了重载服务,关闭服务,服务状态查询以及投递单个异步任务

    <?php
    	use jean\lib\SingleTask;
		use jean\swoolecron\Client;
		
		$client = new Client($ip,$port);
        $task = new SingleTask(
            [
                'callbackType'=>'script',//回调类型(rest,script)口回调
                'script'=>'/var/www/project/market/yii swoole-server/test',//回调的脚本命令或URL
                'requestType'=>'get',//请求的类型（rest脚本专属）get,post,put,deleteelete
                'data'=>[1,2,3,4],
            ]
        );
        $rs = $client->job($task);//投递一个异步任务

		$client->stopServer();//关闭服务
		$client->reloadServer();//重载服务
		$client->serverStatus();//关闭服务




<?php
/**
 * filename: task.php
 * Created by pjianwei.
 * Date: 2016/3/18 14:59
 * description:
  [
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
 */

/**
 * 任务配置（服务启动或运行中自动加载的任务）
 */
return [

];
<?php
/**
 * filename: Job.php
 * Created by pjianwei.
 * Date: 2016/3/17 10:30
 * description:
 */

namespace jean\swooleCron;

use jean\lib\Process;

class Job
{
    /**
     * 异步执行任务
     * @param array $params 回调执行的参数
     * @example $params = [
     *  'work_num'=>3,//不给默认为1
     *  'fun' =>[Server::start] //PHP可执行回调的函数
     *  'data'=>[];//回调函数的参数
     * ]
     */
    static function run(array $params = [])
    {
        return (new Process($params))->run();
    }
}
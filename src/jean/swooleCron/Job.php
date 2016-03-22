<?php
/**
 * filename: Job.php
 * Created by pjianwei.
 * Date: 2016/3/17 10:30
 * description:
 */

namespace jean\swoolecron;

use jean\lib\Process;

/**
 * 本类提供两个方法,功能相同,一个同步调用,一个异步调用
 * 本类同步调用方法使用场景：一个请求有多个耗时的查询或计算,可以通过此方法同步多任务并行执行,以节约时间.
 * Class Job
 * @package jean\swoolecron
 * @author pjianwei{pjianwei@chexiu.cn}
 */
class Job
{
    /**
     * 同步多进程并发执行任务.回调函数自行处理异常，返回值必须为真.否则进程会出异常
     * @param array $params 回调执行的参数
     * @example $params = [
     *  'work_num'=>3,//不给默认为1,最大值为10
     *  'fun' =>[Server::start] //PHP可执行回调的函数.可以统一给一个回调函数,也可以指定索引下标分别指定回调函数
     *  'data'=>[
     *   0=>[],//索引为0的进程回调的参数
     *   1=>[],//索引为1的进程回调函数的参数
     *  ];//回调函数的参数
     * @return array //按照下标索引分别返回每个进程回调函数的结果,并且每个回调函数返回的结果字符大小限制在8K以内
     */
    static function syncRun(array $params = [])
    {
        return (new Process($params))->syncRun()->result();
    }

    static function asyncRun(array $params)
    {
        return (new Process($params))->asyncRun();
    }
}
<?php
/**
 * filename: Task.php
 * Created by pjianwei.
 * Date: 2016/3/11 20:51
 * description:
 */

namespace jean\lib;


class Task extends BaseObject
{
    /**
     * 任务开始时间戳
     * @var integer $on
     */
    protected $on = null;
    /**
     * 任务间隔执行时间秒
     * @var int $interval
     */
    protected $interval = 60;

    protected $taskType = null;

    static function run(callable $fun, $params = [])
    {

    }
}
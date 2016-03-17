<?php
/**
 * filename: TaskType.php
 * Created by pjianwei.
 * Date: 2016/3/11 20:37
 * description:
 */

namespace jean\lib;

/**
 * 任务类型枚举类
 * @package jean\lib
 * @author pjianwei{pjianwei@chexiu.cn}
 */
class TaskType
{
    /**
     *  只执行一次
     */
    const ONE_TIME = 'ONE TIME';

    /**
     * 每天执行一次
     */
    const EVERY_DAY = 'EVERY DAY';

    /**
     * 自定义
     */
    const INTERVAL = 'INTERVAL';
}
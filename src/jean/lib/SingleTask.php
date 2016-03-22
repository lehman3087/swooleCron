<?php
/**
 * filename: SingleTask.php
 * Created by pjianwei.
 * Date: 2016/3/22 11:04
 * description:
 */

namespace jean\lib;

/**
 * 单次任务,设定任务回调类型,接口以及需要传递的数据就好.其它想属性忽略
 * Class SingleTask
 * @package jean\lib
 * @author pjianwei{pjianwei@chexiu.cn}
 */
class SingleTask extends Task
{


    public function __construct(array $params)
    {
        parent::__construct([]);
        foreach ($params as $key => $val) {
            $this->$key = $val;
        }
        $this->taskId = 0;
        $this->taskType = 'single';
    }

    public function update(array $config)
    {
        return $this;
    }
}
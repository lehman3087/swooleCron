# swooleCron
个人学习
$rs = Job::syncRun([
            'worker_num' => 6,//任务并发数
            'fun' => [//每个任务的可执行方法参数
                array($this, 'test'),
                array($this, 'test1'),
                array($this, 'test'),
                array($this, 'test1'),
                array($this, 'test'),
                array($this, 'test1'),
                array($this, 'test'),
                array($this, 'test1'),
                array($this, 'test'),
                array($this, 'test1'),
            ],
            'data' => [//和回调方法参数一一对应,传入回调方法的参数
                  1,
                  2,
                  3,
                  4,
                  5,
                  6,
                  7, 
                  8, 
                  9, 
                  10
            ],
        ]);
  返回值是数组：对应下标的值对应同下标任务的返回结果

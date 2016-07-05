<?php
/**
 * filename: Queue.php
 * Created by pjianwei.
 * Date: 2016/3/17 10:32
 * description:
 */

namespace jean\swoolecron;

use jean\lib\BaseObject;
use jean\lib\Beanstalk;
use jean\lib\Exception;

!defined('DS') and define('DS', DIRECTORY_SEPARATOR);
!defined('SRC') and define('SRC', dirname(dirname(__DIR__)) . DS);


class Queue extends BaseObject
{
    protected $host = null;
    protected $port = null;
    protected $tube = 'default';
    protected $persistent = true;
    protected $timeout = 1;
    protected $logger = null;
    protected static $beanstalk = [];

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->useTube($this->tube);
    }

    /**
     * 初始化当前对象的Beanstalk对象.根据IP和端口获取单例对象
     * @throws Exception
     * @return Beanstalk
     */
    private function getBeanstalk()
    {
        $key = md5($this->host . $this->port);
        $beanstalk = null;
        if (!isset(self::$beanstalk[$key])) {
            $beanstalk = new Beanstalk([
                'persistent' => boolval($this->persistent),
                'host' => !empty($this->host) ? $this->host : '127.0.0.1',
                'port' => intval($this->port) ? intval($this->port) : 11300,
                'timeout' => intval($this->timeout) ? intval($this->timeout) : 1,
                'logger' => (is_callable($this->logger) && method_exists($this->logger, 'error')) ? $this->logger : null,
            ]);
        } else {
            $beanstalk = self::$beanstalk[$key];
        }
        if ($beanstalk->connect()) {
            return self::$beanstalk[$key] = $beanstalk;
        }
        throw new Exception("beanstalkd is disconnect" . __FILE__ . __LINE__);
    }

    /**
     * @param $key
     * @return $this
     * @throws Exception
     */
    public function useTube($key)
    {
        $this->tube = strval($key);
        if (empty($key) || !$this->getBeanstalk()->useTube($key)) {
            throw new Exception("tube $key not found:" . __FILE__ . '-' . __LINE__, '-1');
        }
        return $this;
    }

    public function put($data, $pri = 1024, $delay = 0, $ttr = 84600)
    {
        return $this->useTube($this->tube)->getBeanstalk()->put($pri, $delay, $ttr, $data);
    }

    public function delete($id)
    {
        return $this->useTube($this->tube)->getBeanstalk()->delete($id);
    }

    public function release($id, $pri, $delay = 0)
    {
        return $this->useTube($this->tube)->getBeanstalk()->release($id, $pri, $delay);
    }

    public function pop()
    {
        return $this->useTube($this->tube)->getBeanstalk()->reserve(1);
    }

    /**
     * @return null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param null $host
     * @return Queue
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return null
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param null $port
     * @return Queue
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return null
     */
    public function getTube()
    {
        return $this->tube;
    }

    /**
     * @param null $tube
     * @return Queue
     */
    public function setTube($tube)
    {
        $this->tube = $tube;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return Queue
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }


}
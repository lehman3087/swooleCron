<?php
/**
 * filename: redis.php
 * Created by pjianwei.
 * Date: 2016/3/18 17:18
 * description:
 */

namespace jean\lib;


use jean\swooleCron\Server;

class Redis extends \Redis
{
    static $timeOut = 20;//REDIS锁超时时间
    static $lockName = 'redis_2016_pjw';//redis锁名称
    private $locked = false;

    public function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new Exception('extension redis is not exist', '2');
        }
        parent::__construct();
        $config = Server::$app;
        $this->connect($config['redis']['host'], $config['redis']['port']);
    }

    static function getLockName()
    {
        return md5(self::$lockName);
    }

    public function lock()
    {
        $expire_at = time() + self::$timeOut;
        do {
            $r = $this->setnx(self::getLockName(), time());
            if ($this->locked || $r) {
                $this->expire(self::getLockName(), self::$timeOut);
                $this->locked = true;
                break;
            }
            usleep(10000);
        } while (time() <= $expire_at);
        return $this->locked;
    }

    public function getObject()
    {
        $rs = json_decode($this->get(Server::$app['table_key']));
        return is_object($rs) ? get_object_vars($rs) : $rs;
    }

    public function setObject($data = [])
    {
        return $this->set(Server::$app['table_key'], json_encode($data));
    }

    public function unlock()
    {
        $this->locked = false;
        $this->del(self::getLockName());
    }

    public function __destruct()
    {
        try {
            if ($this->ping()) {
                $this->unlock();
                $this->close();
            }
        } catch (\Exception $e) {
        }
    }
}
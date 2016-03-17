<?php
/**
 * filename: Exception.php
 * Created by pjianwei.
 * Date: 2016/3/14 9:38
 * description:
 */

namespace jean\lib;


class Exception extends \Exception
{
    public function __construct($message, $code, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
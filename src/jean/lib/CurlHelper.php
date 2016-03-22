<?php
/**
 * filename: Curl.php
 * Created by pjianwei.
 * Date: 2016/3/18 18:30
 * description:
 */

namespace jean\lib;

class CurlHelper
{
    static $contentType = "application/json";

    static function curlGet($curlUrl, $data = [])
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', self::$contentType);
        try {
            $curl->get($curlUrl . '?' . http_build_query($data));
        } catch (\Exception $e) {
            return false;
        }
        if (isset($curl->response->code)) {
            return $curl->response;
        }
        return false;
    }

    static function curlPost($curlUrl, $data = [])
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', self::$contentType);
        try {
            $curl->post($curlUrl, $data);

        } catch (\Exception $e) {
            return $curl->errorMessage;
        }
        if (isset($curl->response->code)) {
            return $curl->response;
        }
        return false;
    }

    static function curlPut($curlUrl, $data = [])
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', self::$contentType);
        try {
            $curl->put($curlUrl, $data);
        } catch (\Exception $e) {
            return $curl->errorMessage;
        }
        if (isset($curl->response->code)) {
            return $curl->response;
        }
        return false;
    }

    static function curlDelete($curlUrl, $data = [])
    {
        $curl = new Curl();
        $curl->setHeader('Content-Type', self::$contentType);
        try {
            $curl->delete($curlUrl . '?' . http_build_query($data));
        } catch (\Exception $e) {
            return $curl->errorMessage;
        }
        if (isset($curl->response->code)) {
            return $curl->response;
        }
        return false;
    }
}
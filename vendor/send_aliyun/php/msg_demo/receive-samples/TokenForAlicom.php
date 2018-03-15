<?php

/**
 * 用于接收云通信消息的临时token
 *
 */

class TokenForAlicom
{
	private $messageType;
	private $token;
	private $expireTime;
	private $tempAccessKey;
	private $tempSecret;
	private $client;
	private $queue;

	public function setMessageType($messageType)
    {
        $this->messageType = $messageType;
    }

    public function getMessageType()
    {
        return $this->messageType;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setExpireTime($expireTime)
    {
        $this->expireTime = $expireTime;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    public function setTempAccessKey($tempAccessKey)
    {
        $this->tempAccessKey = $tempAccessKey;
    }

    public function getTempAccessKey()
    {
        return $this->tempAccessKey;
    }

    public function setTempSecret($tempSecret)
    {
        $this->tempSecret = $tempSecret;
    }

    public function getTempSecret()
    {
        return $this->tempSecret;
    }

    public function setClient($client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    public function getQueue()
    {
        return $this->queue;
    }



}

?>
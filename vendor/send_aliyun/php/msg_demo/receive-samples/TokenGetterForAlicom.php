<?php

/**
 * 用于接收云通信消息的临时token
 *
 */
#require_once dirname(dirname(__FILE__)).'/aliyun-php-sdk-core/Config.php';  
use Dybaseapi\Request\V20170525 as Dybaseapi;  

use TokenForAlicom as TokenForAlicom;
use AliyunMNS\Client;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;


class TokenGetterForAlicom
{
	private $accesskey;
    private $accessSecret;  
    private static $iAcsClient;
    private $bufferTime=60*2;//过期时间小于2分钟则重新获取，防止服务器时间误差
    private $mnsAccountEndpoint="http://1943695596114318.mns.cn-hangzhou.aliyuncs.com/";
    private static $tokenMap;
    public static function findMapByMessageType($messageType)
    {
        foreach (self::$tokenMap as $key => $tokenForAlicom) {
            if ($tokenForAlicom->getMessageType() == $messageType) {
                return $tokenForAlicom;
            }
        }
    }
    public static function addTokenForAlicom($messageType, $tokenForAlicom)
    {
        if( NULL == self::$tokenMap ){
            self::$tokenMap = array();
        }
        array_push(self::$tokenMap, $tokenForAlicom);
    }
    
    public static function updateTokenForAlicom($messageType, $tokenForAlicom)
    {
        $old = self::findMapByMessageType($messageType);
        if (null == $old ) {
            array_push(self::$tokenMap, $tokenForAlicom);
        }
        else {
            $old = $client;
        }
    }


    public function __construct($accesskey,$accessSecret) {        
        $this->accesskey = $accesskey;
        $this->accessSecret = $accessSecret;      
        $this->init($accesskey,$accessSecret);                
    }


    public function init($accessKey,$accessSecret) {
       #DefaultProfile::addEndpoint("cn-hangzhou","cn-hangzhou","Dybaseapi","dybaseapi.aliyuncs.com");
       $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $accessKey, $accessSecret);       
       echo $accesskey;
       $this->iAcsClient = new DefaultAcsClient($iClientProfile);  
     
    }

    public function getTokenFromRemote($messageType) {             
        $request = new Dybaseapi\QueryTokenForMnsQueueRequest();
        $request->setMessageType($messageType); 
        try {
            $response = $this->iAcsClient->getAcsResponse($request);      
            #print_r($response);      
            $tokenForAlicom = new TokenForAlicom();
            $tokenForAlicom->setMessageType($messageType);   
            $tokenForAlicom->setToken($response->MessageTokenDTO->SecurityToken);
            $tokenForAlicom->setTempAccessKey($response->MessageTokenDTO->AccessKeyId);
            $tokenForAlicom->setTempSecret($response->MessageTokenDTO->AccessKeySecret);           
            $tokenForAlicom->setExpireTime($response->MessageTokenDTO->ExpireTime);
            #print_r($tokenForAlicom);
            return $tokenForAlicom;
        }
        catch (ClientException  $e) {
            print_r($e->getErrorCode());   
            print_r($e->getErrorMessage());   
        }
        catch (ServerException  $e) {        
            print_r($e->getErrorCode());   
            print_r($e->getErrorMessage());
        }       
    }

    public function getToeknByMessageType($messageType , $queueName ,$mnsAccountEndpoint = "http://1943695596114318.mns.cn-hangzhou.aliyuncs.com/" ) {

        $tokenForAlicom = self::findMapByMessageType($messageType);
        if(NULL == $tokenForAlicom ) {
            $tokenForAlicom =$this->getTokenFromRemote($messageType);
            $client = new Client($mnsAccountEndpoint, $tokenForAlicom->getTempAccessKey(), $tokenForAlicom->getTempSecret() , $tokenForAlicom->getToken());
            #print_r($client);
            $tokenForAlicom->setClient($client);
            $tokenForAlicom->setQueue($queueName);
            self::addTokenForAlicom($messageType,$tokenForAlicom);
        }
        else {

            $now = date_create(NULL,timezone_open("Asia/Chongqing"));
            $expireTime = date_create($tokenForAlicom->getExpireTime(),timezone_open("Asia/Chongqing"));
            if(($expireTime-$now)>$bufferTime){
                $tokenForAlicom =$this->getTokenFromRemote($messageType);
            }
            else {
                return $tokenForAlicom;
            }
        }
        
        $client = new Client($mnsAccountEndpoint, $tokenForAlicom->getTempAccessKey(), $tokenForAlicom->getTempSecret() , $tokenForAlicom->getToken());
        #print_r($client);
        $tokenForAlicom->setClient($client);
        $tokenForAlicom->setQueue($queueName);
        self::updateTokenForAlicom($messageType,$tokenForAlicom);
        return $tokenForAlicom;
       
    }


}

?>
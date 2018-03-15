<?php
require_once(dirname(dirname(__FILE__)).'/mns-autoloader.php');
#include_once 'aliyun-php-sdk-core/Config.php';
require_once dirname(dirname(__FILE__)).'/aliyun-php-sdk-core/Config.php';  
#echo dirname(dirname(__FILE__))."<br>";
#include_once dirname(dirname(__FILE__)).'/aliyun-php-sdk-core/Config.php';
require_once (dirname(__FILE__)).'/TokenGetterForAlicom.php';
require_once (dirname(__FILE__)).'/TokenForAlicom.php';
// 代码里需要用的一些php class
use AliyunMNS\Client;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;
use TokenGetterForAlicom;
use TokenForAlicom;
use Dybaseapi\Request\V20170525 as Dybaseapi;  
use Dysmsapi\Request\V20170525 as Dysmsapi; 

class ReceiveAlicomMsgDemo {
	public function dealMessage($message) {
		
		echo $message;
		//TODO 这里开始写业务代码
		return true;//返回true，则工具类自动删除已拉取的消息。返回false,消息不删除可以下次获取
	}

	public function receiveMsg(){		
		
		$accessId = "your_accessKey";
		$accessKey = "your_accessSecret";
		$messageType = "SmsReport";//短信回执：SmsReport，短息上行：SmsUp，语音呼叫：VoiceReport，流量直冲：FlowReport
		$queueName = "your_queueName"; //在云通信页面开通相应业务消息后，就能在页面上获得对应的queueName

		DefaultProfile::addEndpoint("cn-hangzhou","cn-hangzhou","Dybaseapi","dybaseapi.aliyuncs.com");
		
		$tokenGetterForAlicom = new TokenGetterForAlicom($accessKeyID,$accessKeySecret);
		
        $i = 0; 
        while ( $i <= 3) {//取回执消息失败5 次停止循环拉取
        	
            $i++;
	        try
	        {
	        	$tokenForAlicom = $tokenGetterForAlicom->getToeknByMessageType($messageType,$queueName);	
                $queue = $tokenForAlicom->getClient()->getQueueRef($queueName);
		        // 3. receive message
		        $receiptHandle = NULL;
	            // when receiving messages, it's always a good practice to set the waitSeconds to be 30.
	            // it means to send one http-long-polling request which lasts 30 seconds at most.
	            $res = $queue->receiveMessage(2);
	            echo "ReceiveMessage Succeed! \n";
	            $bodyMD5 = md5(base64_encode($res->getMessageBody()));
	            $receiptHandle = $res->getReceiptHandle();
	            if (strtoupper($bodyMD5) == $res->getMessageBodyMD5())
	            {
	                if($this->dealMessage($res->getMessageBody())){
	                	$res = $queue->deleteMessage($receiptHandle);
        				echo "DeleteMessage Succeed! \n";
	                }
	            }
	        }
	        catch (MnsException $e)
	        {
	            echo "ex:".($e->getMnsErrorCode()) ;
	            echo "ReceiveMessage Failed: " . $e;
	            echo "<br>";
	            #return;
	        }
        }
	}
}


$demo = new  ReceiveAlicomMsgDemo();
$demo->receiveMsg();
echo "<br>end";
?>
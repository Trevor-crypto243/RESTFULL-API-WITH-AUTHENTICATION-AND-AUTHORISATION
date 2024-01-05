<?php
/**
 * Created by PhpStorm.
 * User: muoki
 * Date: 2019-10-09
 * Time: 15:46
 * @param $recipientNumber
 * @param $message
 */

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

function send_sms($recipientNumber, $message){

    $payload = array(
        "recipient"=>$recipientNumber,
        "message"=>$message,
    );

    $connection = new AMQPStreamConnection('localhost', 5672,
        'guest', 'guest');
    $channel = $connection->channel();
    $channel->queue_declare('SMS_OUTBOX_QUEUE', false, true, false, false);
    $msg = new AMQPMessage(json_encode($payload), array('delivery_mode' => 2)
    );
    $channel->basic_publish($msg, '', 'SMS_OUTBOX_QUEUE');
    $channel->close();
    $connection->close();

}


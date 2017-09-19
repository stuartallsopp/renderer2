<?php
/**
 * Created by PhpStorm.
 * User: stuartallsopp
 * Date: 12/09/2017
 * Time: 10:44
 */

namespace AppBundle\Rabbit;


use AppBundle\Workers\UploadWorker;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitAction implements ConsumerInterface
{
    private $container;
    private $producer;
    private $doctrine;
    public function __construct($container)
    {
        $this->container=$container;
        $this->producer=$this->container->get('old_sound_rabbit_mq.render_action_producer');
        $this->doctrine=$this->container->get('doctrine')->getManager();
    }

    public function execute(AMQPMessage $msg)
    {
        $settings=self::getSettings();
        $body=json_decode($msg->getBody(),true);
        if ($body['to']=='anyone'||$body['to']==$settings->getNodeName())
        {
           switch ($body['task'])
           {
               case 'takesession':
                   $result=UploadWorker::acknowledgeSession($settings->getNodeName(),$body['from'],$body['id'],$this->producer);
                   return true;
               case 'acksession':
                   $result=UploadWorker::updateSession($body['from'],$body['id'],$this->doctrine);
                   $result=UploadWorker::sendFile($settings->getNodeName(),$body['from'],$body['id'],$this->producer,$this->doctrine);
                   return true;
               case 'sendfile':
                    $result=UploadWorker::storeInQueue($body['from'],$settings,$body['id'],$body['start'],$body['end'],$body['content'],$body['instance'],$body['count'],$body['no'],$this->producer,$this->doctrine);
                    return true;
               case 'gotfile':
                   $result=UploadWorker::ackgotFile($body['id'],$this->doctrine);
                   return true;
               case 'render':
                   $result=UploadWorker::createDirectory($body['id'],$settings);
                   $result=UploadWorker::moveFile($body['id'],$this->doctrine,$settings);
                   $result=UploadWorker::blendQueue($body['id'],$this->doctrine,$this->producer,$settings);
                   $result=UploadWorker::cleanUp($body['id'],$settings);
                   return true;
               case 'sendimage':
                   $result=UploadWorker::storeAsFrame($body['id'],$body['frame'],$body['content'],$body['from'],$this->doctrine,$this->producer,$settings);
                   return true;
               case 'sendimageack':
                   $result=UploadWorker::updateFileSendAcknowledge($body['id'],$body['frame'],$this->doctrine);
                   return true;
               case 'attachfile':
                   $result=UploadWorker::createEntityFromFile($body['path'],$body['size'],$body['id'],$this->doctrine);
                   return true;
               case 'cleanup':
                   $result=UploadWorker::doCleanUp($body['id'],$this->doctrine,$settings);
                   return true;
           }
        }
        return false;
    }


    private function getSettings()
    {
        $doctrine=$this->container->get('doctrine')->getManager();
        $rep=$doctrine->getRepository('AppBundle:Setting');
        $list=$rep->findAll();
        return $list[0];
    }
}
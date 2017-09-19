<?php
/**
 * Created by PhpStorm.
 * User: stuartallsopp
 * Date: 14/09/2017
 * Time: 12:02
 */

namespace AppBundle\Listeners;


use AppBundle\Entity\Upload;
use AppBundle\Workers\UploadWorker;
use Doctrine\Common\Persistence\ObjectManager;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Symfony\Component\HttpFoundation\File\File;

class UploadListener
{
    /**
     * @var ObjectManager
     */
    private $om;
    private $container;

    public function __construct(ObjectManager $om,$container)
    {
        $this->om = $om;
        $this->container=$container;
        set_time_limit(9000);
    }

    public function onUpload(PostPersistEvent $event)
    {
        set_time_limit(9000);

        $producer=$this->container->get('old_sound_rabbit_mq.render_action_producer');
        $repository=$this->om->getRepository('AppBundle:Setting');
        $settings=$repository->findAll();
        $settings=$settings[0];

        $upload=new Upload();
        $this->om->persist($upload);
        $this->om->flush();
        $file=$event->getFile();
        UploadWorker::createFileUploadEvent($producer,$settings->getNodeName(),$file->getRealPath(),$file->getFileInfo()->getSize(),$upload->getId());
        $response = $event->getResponse();
        $response['success'] = true;
        $response['id']=$upload->getId();

        return $response;
    }
}
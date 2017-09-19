<?php
/**
 * Created by PhpStorm.
 * User: stuartallsopp
 * Date: 12/09/2017
 * Time: 09:56
 */

namespace AppBundle\Workers;


use AppBundle\Entity\Frame;
use AppBundle\Entity\Queue;
use AppBundle\Entity\Queuechunk;
use AppBundle\Entity\Queueframe;
use AppBundle\Entity\Session;
use AppBundle\Entity\Setting;
use AppBundle\Entity\Upload;
use AppBundle\Entity\Uploadchunk;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\PropertyAccess\Tests\Fixtures\Ticket5775Object;

class UploadWorker
{

    public static function splitUpload(Upload $upload)
    {
        $threads=$upload->getThreads();
        $frames=$upload->getFrameend()-$upload->getFramestart();
        print_r('frames '.$frames);
        $split=intval($frames/$threads);
        print_r('split '.$split);
        $start=$upload->getFramestart();
        while ($start<=$upload->getFrameend())
        {
            $item=new Session();
            $item->setFramestart($start);
            if (($start+$split)>$upload->getFrameend())
            {
                $item->setFrameend($upload->getFrameend());
            }else
            {
                $item->setFrameend($start+$split);
            }
            $item->setParentid($upload);
            $item->setStatus(0);
            $item->setSessionkey(self::GUID());
            $item->setNodename(null);
            $upload->addSession($item);
            $start=$start+($split+1);
        }
        return $upload;
    }

    public static function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }


    public static function sendSession(Session $session,Setting $settings,Producer $producer)
    {
        $message=[];
        $message['from']=$settings->getNodename();
        $message['to']='anyone';
        $message['task']='takesession';
        $message['id']=$session->getSessionkey();

        return self::sendMessage($message,$producer);

    }

    public static function updateSession($from,$sessionid,ObjectManager $doctrine)
    {
        $repository=$doctrine->getRepository('AppBundle:Session');
        $session=$repository->findOneBy(array('sessionkey'=>$sessionid));
        if ($session)
        {

                $session->setNodeName($from);
                $session->setStatus(2);
                $doctrine->persist($session);
                $doctrine->flush();
                return true;

        }
        return false;
    }

    private static function checkUploadForPublished($id,ObjectManager $doctrine)
    {
        $repository=$doctrine->getRepository('AppBundle:Upload');
        $upload=$repository->find($id);
        if ($upload->hasEmptySessions()==false)
        {
            $upload->setStatus(3);
            $doctrine->persist($upload);
            $doctrine->flush();
        }
    }

    public static function storeInQueue($from,Setting $setting,$sessionid,$start,$finish,$content,$instance,$count,$no,Producer $producer,EntityManager $doctrine)
    {
        set_time_limit(6000);
        ini_set("memory_limit", -1);
        gc_enable();
        $doctrine->getConnection()->getConfiguration()->setSQLLogger(null);
        $repository=$doctrine->getRepository('AppBundle:Queue');
        $queue=$repository->findOneBy(array('sessionkey'=>$sessionid,'instancekey'=>$instance));
        if ($queue==null)
        {
            $queue=new Queue();
            $queue->setSessionkey($sessionid);
            $queue->setInstancekey($instance);
            $queue->setSourcenode($from);
        }
        $queue->setChunkcount($count);
        $queue->setFramestart($start);
        $queue->setFrameend($finish);
        $chunk=$queue->getChunkByNo($no);
        if ($chunk==null)
        {
            $chunk=new Queuechunk();
            $chunk->setQueueid($queue);
            $chunk->setChunkno($no);
            $queue->addChunk($chunk);
            $chunk->setInstancekey($instance);
        }
        $chunk->setContent(base64_decode($content));
        print_r(sizeof($chunk->getContent()));
        $doctrine->persist($queue);
        $doctrine->flush();
        gc_collect_cycles();
        if (sizeof($queue->getChunks())==$queue->getChunkcount())
        {
            return self::gotFile($from,$setting->getNodename(),$sessionid,$producer);
        }
        $doctrine->clear();
        return true;
    }

    public static function gotFile($to,$from,$sessionid,Producer $producer)
    {
        $message=[];
        $message['to']=$to;
        $message['from']=$from;
        $message['task']='gotfile';
        $message['id']=$sessionid;
        return self::sendMessage($message,$producer);
    }

    public static function ackgotFile($sessionid,ObjectManager $doctrine)
    {
        $repository=$doctrine->getRepository('AppBundle:Session');
        $session=$repository->findOneBy(array('sessionkey'=>$sessionid));
        if ($session)
        {
            $session->setStatus(3);
            $doctrine->persist($session);
            $doctrine->flush();
            self::checkUploadForPublished($session->getParentId(),$doctrine);
        }
        return true;
    }

    public static function sendFile($from,$to,$sessionid,Producer $producer,EntityManager $doctrine)
    {
        set_time_limit(6000);
        ini_set("memory_limit", -1);
        gc_enable();
        $doctrine->getConnection()->getConfiguration()->setSQLLogger(null);
        $instance=self::GUID();
        $repository=$doctrine->getRepository('AppBundle:Session');
        $session=$repository->findOneBy(array('sessionkey'=>$sessionid));
        $file=$session->getParentId();
        foreach($file->getChunks() as $chunk)
        {
            $strm=stream_get_contents($chunk->getContent());
            $message=[];
            $message['to']=$to;
            $message['from']=$from;
            $message['task']='sendfile';
            $message['id']=$sessionid;
            $message['start']=$session->getFrameStart();
            $message['end']=$session->getFrameEnd();
            $message['count']=$file->getChunkCount();
            $message['no']=$chunk->getChunkNo();
            $message['instance']=$instance;
            $message['size']=sizeof($strm);
            $message['content']=base64_encode($strm);
            self::sendMessage($message,$producer);
            gc_collect_cycles();
        }
        return true;
    }

    public static function acknowledgeSession($from,$to,$sessionid,Producer $producer)
    {
        $message=[];
        $message['from']=$from;
        $message['to']=$to;
        $message['task']='acksession';
        $message['id']=$sessionid;
        return self::sendMessage($message,$producer);
    }

    private static function sendMessage($message,Producer $producer,$priority=0)
    {
        $args=[];
        $args['x-priority']=$priority;
        $producer->publish(json_encode($message),'',$args);
        return true;
    }

    public static function publishSessions(Upload $upload,ObjectManager $manager,Producer $producer,Setting $settings)
    {
        foreach($upload->getSessions() as $session)
        {
            $status=self::sendSession($session,$settings,$producer);
            if ($status==true)
            {
                $session->setStatus(1);
            }
        }
        $upload->setStatus(2);
        $manager->persist($upload);
        $manager->flush();
        return true;
    }

    public static function renderSession(Session $session,ObjectManager $manager,Producer $producer,Setting $settings)
    {
        $upload=$session->getParentid();
        $upload->setStatus(4);
        $session->setStatus(0);
        self::sendRender($producer,$session->getSessionKey(),$settings->getNodename(),$session->getNodename());
        $manager->persist($session);
        $manager->persist($upload);
        $manager->flush();
    }

    public static function createDirectory($sessionid,Setting $setting)
    {
        $dirpath=$setting->getBlendpath();
        chdir($dirpath);
        if (file_exists($sessionid)==false)
        {
            mkdir($sessionid);
        }
        return true;
    }

    public static function doCleanUp($sessionid,ObjectManager $manager,Setting $settings)
    {
        $repository=$manager->getRepository('AppBundle:Queue');
        $list=$repository->findBy(array('sessionkey'=>$sessionid,'status'=>0));
        foreach($list as $item)
        {
            $manager->remove($item);
        }
        $manager->flush();
        $list=$repository->findBy(array('sessionkey'=>$sessionid,'status'=>2));
        foreach($list as $item)
        {
            $manager->remove($item);
        }
        $manager->flush();
        self::cleanUp($sessionid,$settings);
        return true;
    }

    public static function sendCleanUp($id,ObjectManager $manager,Producer $producer)
    {
        $setrep=$manager->getRepository('AppBundle:Setting');
        $setlist=$setrep->findAll();
        if (sizeof($setlist)==0){return;}
        $settings=$setlist[0];
        $repository=$manager->getRepository('AppBundle:Upload');
        $upload=$repository->find($id);
        if ($upload)
        {
            foreach($upload->getSessions() as $session)
            {
                if ($session->getNodeName()!=null)
                {
                    $message=[];
                    $message['to']=$session->getNodeName();
                    $message['from']=$settings->getNodeName();
                    $message['task']='cleanup';
                    $message['id']=$session->getSessionKey();
                    self::sendMessage($message,$producer);
                }
            }
        }
    }

    public static function blendQueue($sessionid,ObjectManager $manager,Producer $producer,Setting $settings)
    {
        $repository=$manager->getRepository('AppBundle:Queue');
        $queue=$repository->findOneBy(array('sessionkey'=>$sessionid));
        if ($queue)
        {
            $queue->setStatus(1);
            $manager->persist($queue);
            $manager->flush();
            $frameno=$queue->getFrameStart();
            while($frameno<=$queue->getFrameEnd())
            {
                $frame=self::blendFrame($queue,$frameno,$manager,$settings);
                if ($frame)
                {
                    $result=self::sendFrame($producer,$frame,$settings);
                }
                $frameno++;
            }
            $queue->setStatus(2);
            $manager->persist($queue);
            $manager->flush();
        }
        return true;
    }



    private static function blendFrame(Queue $queue,$frameno,ObjectManager $manager,Setting $settings)
    {
        $frame=$queue->getFramebyNo($frameno);
        if ($frame==null)
        {
            $frame=new Queueframe();
            $frame->setQueueid($queue);
            $frame->setFrameno($frameno);
            $frame->setStatus(0);
        }
        $frame->setStartdatetime(new \DateTime('now'));
        $manager->persist($frame);
        $manager->flush();
        self::renderFrame($settings,$frameno,$queue);
        $frame->setEnddatetime(new \DateTime('now'));
        self::setFile($frame,$settings,$queue,$frameno,$manager);
        return $frame;
    }

    public static function createFileUploadEvent(Producer $producer,$node,$path,$size,$id)
    {
        $message=[];
        $message['to']=$node;
        $message['from']=$node;
        $message['task']='attachfile';
        $message['path']=$path;
        $message['size']=$size;
        $message['id']=$id;
        self::sendMessage($message,$producer);
    }

    public static function createEntityFromFile($path,$size,$id,EntityManager $manager)
    {
        set_time_limit(6000);
        ini_set("memory_limit", -1);
        gc_enable();
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);
        $repository=$manager->getRepository('AppBundle:Upload');
        $upload=$repository->find($id);
        if ($upload==null)
        {
            $upload=new Upload();
        }
        $fileinfo=pathinfo($path);
        $strm = fopen($path,'rb');
        print_r('streaming');
        $offset=0;
        $chunksize=1000000;
        if ($strm === false) {
            return false;
        }
        $counter=1;
        $upload->setFilesize($size);
        $upload->setStatus(1);
        $upload->setChunkcount(0);
        while (!feof($strm)) {
            $buffer = fread($strm, $chunksize);
            $chunk=new Uploadchunk();
            $chunk->setUploadid($upload);
            $chunk->setChunkno($counter);
            $upload->setChunkcount($counter);
            $chunk->setContent($buffer);
            $upload->addChunk($chunk);
            $counter++;
        }
        if ($upload->getSessions()->count()==0 && $upload->getFramestart()>0 && $upload->getFrameend()>0)
        {
            $upload=self::splitUpload($upload);
        }
        $manager->persist($upload);
        $manager->flush($upload);
        $status = fclose($strm);

        $manager->clear(); // clear doctrine managed entities
        gc_collect_cycles();
        unlink($path);
        print_r('flushing');
        return true;
    }

    private static function setFile(Queueframe &$frame,Setting $settings,Queue $queue,$frameno,ObjectManager $manager)
    {
        $framestr=str_pad($frameno, 5, '0', STR_PAD_LEFT);
        $filename='frame_'.$framestr.'.png';
        $fullpath=$settings->getBlendpath().'/'.$queue->getSessionkey().'/'.$filename;
        if (file_exists($fullpath)==true)
        {
            $strm = fopen($fullpath,'rb');
            $frame->setContent(stream_get_contents($strm));
            $frame->setFilesize(filesize($fullpath));
            $manager->persist($frame);
            $manager->flush();
            fclose($strm);
        }
    }

    private static function killFile(Queueframe $frame,Setting $settings,Queue $queue,$frameno)
    {
        $framestr=str_pad($frameno, 5, '0', STR_PAD_LEFT);
        $filename='frame_'.$framestr.'.png';
        $fullpath=$settings->getBlendpath().'/'.$queue->getSessionkey().'/'.$filename;
        if (file_exists($fullpath)==true)
        {
            unlink($fullpath);
        }
    }

    public static function storeAsFrame($sessionid,$frameno,$content,$from,ObjectManager $manager,Producer $producer,Setting $settings)
    {
        $repository=$manager->getRepository('AppBundle:Session');
        $session=$repository->findOneBy(array('sessionkey'=>$sessionid));
        if ($session)
        {
            $frame=$session->getFramebyNo($frameno);
            if ($frame==null)
            {
                $frame=new Frame();
                $frame->setSessionid($session);
                $frame->setFrameno($frameno);
            }
            $frame->setContent(base64_decode($content));
            $manager->persist($frame);
            $manager->persist($session);
            $manager->flush();
            self::updateSessionOnNewFrame($session->getId(),$manager);
            self::updateUploadOnNewFrame($session->getParentId()->getId(),$manager);
            self::sendFrameAck($from,$settings->getNodename(),$sessionid,$frameno,$producer);
        }
        return true;
    }

    private static function updateSessionOnNewFrame($id,ObjectManager $manager)
    {
        $repository=$manager->getRepository('AppBundle:Session');
        $session=$repository->find($id);
        $framecount=($session->getFrameEnd()+1)-$session->getFrameStart();
        if ($session->getFrames()->count()>=$framecount)
        {
            $session->setStatus(1);
            $manager->persist($session);
            $manager->flush();
        }
    }

    public static function cleanUp($sessionid,Setting $settings)
    {
        $path=$settings->getBlendpath().'/'.$sessionid;
        if (file_exists($path)==true)
        {
            foreach(glob($path . '/*') as $file) {
                if(is_dir($file)) rrmdir($file); else unlink($file);
            } rmdir($path);
        }
    }


    private static function updateUploadOnNewFrame($id,ObjectManager $manager)
    {
        $repository=$manager->getRepository('AppBundle:Upload');
        $upload=$repository->find($id);
        if ($upload->hasOpenSessions()==false)
        {
            $upload->setStatus(5);
            $manager->persist($upload);
            $manager->flush();
        }
    }

    public static function updateFileSendAcknowledge($sessionid,$frameno,ObjectManager $manager)
    {
        $repository=$manager->getRepository('AppBundle:Queue');
        $queue=$repository->findOneBy(array('sessionkey'=>$sessionid));
        if ($queue)
        {
            $frame=$queue->getFramebyNo($frameno);
            if ($frame)
            {
                $frame->setStatus(1);
                $manager->persist($frame);
            }
        }
        $manager->flush();
    }

    private static function sendFrameAck($to,$from,$sessionid,$frameno,Producer $producer)
    {
        $message=[];
        $message['from']=$from;
        $message['to']=$to;
        $message['task']='sendimageack';
        $message['id']=$sessionid;
        $message['frame']=$frameno;
        self::sendMessage($message,$producer);
    }

    private static function sendFrame(Producer $producer,Queueframe $frame,Setting $settings)
    {
        $message=[];
        $message['to']=$frame->getQueueid()->getSourcenode();
        $message['from']=$settings->getNodename();
        $message['task']='sendimage';
        $message['id']=$frame->getQueueid()->getSessionkey();
        $message['frame']=$frame->getFrameno();
        $message['content']=base64_encode($frame->getContent());
        return self::sendMessage($message,$producer,10);
    }

    private static function renderFrame(Setting $settings,$frameno,Queue $queue)
    {
        $filepath=$settings->getBlendpath().'/'.$queue->getSessionKey().'/source.blend';
        $outputpath=$settings->getBlendpath().'/'.$queue->getSessionKey().'/frame_#####';
        $apppath=$settings->getApplicationpath();
        $cmd=$apppath.' -b '.$filepath.' -o '.$outputpath.' -F PNG -f '.$frameno;
        system($cmd);
    }

    public static function moveFile($sessionid,ObjectManager $doctrine,Setting $settings)
    {
        set_time_limit(6000);
        ini_set("memory_limit", -1);
        gc_enable();
        $repository=$doctrine->getRepository('AppBundle:Queue');
        $queue=$repository->findOneBy(array('sessionkey'=>$sessionid));
        if ($queue)
        {
            $path=$settings->getBlendpath().'/'.$queue->getSessionKey().'/source.blend';
            if (file_exists($path)==true)
            {
                unlink($path);
            }
            $file_handle=fopen($path,'ab');
            foreach($queue->getChunks() as $chunk)
            {
                $write=fwrite($file_handle,stream_get_contents($chunk->getContent()));
            }
            fclose($file_handle);
            gc_collect_cycles();
            return true;
        }
        return false;
    }

    public static function killBlendFile($sessionid,ObjectManager $doctrine,Setting $settings)
    {
        $repository=$doctrine->getRepository('AppBundle:Queue');
        $queue=$repository->findOneBy(array('sessionkey'=>$sessionid));
        if ($queue)
        {
            $path=$settings->getBlendpath().'/'.$queue->getSessionKey().'/source.blend';
            if (file_exists($path)==true)
            {
                unlink($path);
            }
            return true;
        }
        return false;
    }

    public static function sendRender(Producer $producer,$sessionid,$from,$to)
    {
        $message=[];
        $message['to']=$to;
        $message['from']=$from;
        $message['task']='render';
        $message['id']=$sessionid;
        self::sendMessage($message,$producer);
    }

}
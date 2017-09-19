<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Setting;
use AppBundle\Entity\Upload;
use AppBundle\Workers\UploadWorker;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('@App/index.html.twig');
    }

    /**
     * @Route("/uploadform", name="upload_file_form")
     */
    public function uploadformAction()
    {
        return $this->render('@App/upload.html.twig'
            );
    }

    /**
     * @Route("/uploadinfo/{id}", name="upload_file_info")
     */
    public function uploadinfoAction(Request $request,$id)
    {
        $repository=$this->getDoctrine()->getRepository('AppBundle:Upload');
        $upload=$repository->find($id);
        $initialise=false;
        if ($upload==null)
        {
            $initialise=true;
        }
        if ($upload!=null && $initialise==false)
        {
            if ($upload->getStatus()==null)
            {
                $upload->setFilename('New Uploaded Blend');
                $upload->setFramestart(1);
                $upload->setFrameend(250);
                $upload->setThreads(5);
            }
        }
        $previousstart=$upload->getFramestart();
        $previousend=$upload->getFrameend();
        $form = $this->createFormBuilder($upload)
            ->add('filename', TextType::class)
            ->add('framestart', NumberType::class)
            ->add('frameend',NumberType::class)
            ->add('threads',NumberType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($upload->getStatus()==null)
            {
                $upload->setStatus(0);
            }
            if (is_numeric($upload->getThreads())==false)
            {
                $upload->setThreads(1);
            }
            if ($previousend!=$upload->getFrameend() || $previousstart!=$upload->getFramestart())
            {
                if ($upload->getSessions()->count()>0)
                {
                    foreach($upload->getSessions() as $session)
                    {
                        $this->getDoctrine()->getManager()->remove($session);
                        $upload->removeSession($session);
                    }
                }
            }
            if ($upload->getSessions()->count()==0 && $upload->getFramestart()>0 && $upload->getFrameend()>0)
            {
                $upload=UploadWorker::splitUpload($upload);
            }
            $this->getDoctrine()->getManager()->persist($upload);
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse('ok');
        }

        return $this->render('@App/uploadinfo.html.twig', array(
            'form' => $form->createView(),
            'id'=>$id
        ));
    }

    /**
     * @Route("/settings", name="settings_dialog")
     */
    public function settingsAction(Request $request)
    {
        $repository=$this->getDoctrine()->getRepository('AppBundle:Setting');
        $list=$repository->findAll();
        if (sizeof($list)==0)
        {
            $settings=new Setting();
        }else
        {
            $settings=$list[0];
        }

        $form = $this->createFormBuilder($settings)
            ->add('nodename', TextType::class)
            ->add('blendpath',TextType::class)
            ->add('applicationpath',TextType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $this->getDoctrine()->getManager()->persist($settings);
            $this->getDoctrine()->getManager()->flush();
            return new JsonResponse('ok');
        }

        return $this->render('@App/settings.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/upload", name="_uploader_upload_gallery")
     */
    public function uploadAction(Request $request)
    {

     }

    /**
     * @Route("/list", name="upload_list")
     */
    public function uploadlistAction()
    {
        $list=[];
        array_push($list,array('status'=>0,'text'=>'Uploading','colour'=>'danger'));
        array_push($list,array('status'=>1,'text'=>'Uploaded','colour'=>'info'));
        array_push($list,array('status'=>2,'text'=>'Publishing','colour'=>'primary'));
        array_push($list,array('status'=>3,'text'=>'Published','colour'=>'primary'));
        array_push($list,array('status'=>4,'text'=>'Rendering','colour'=>'warning'));
        array_push($list,array('status'=>5,'text'=>'Rendered','colour'=>'success'));
        return $this->render('@App/uploadlistouters.html.twig',array('list'=>$list));
    }

    /**
     * @Route("/queue", name="queue_list")
     */
    public function queuelistAction()
    {
        $list=[];
        array_push($list,array('status'=>0,'text'=>'Queue Ready','colour'=>'danger'));
        array_push($list,array('status'=>1,'text'=>'Queue Working','colour'=>'warning'));
        array_push($list,array('status'=>2,'text'=>'Queue Done','colour'=>'success'));
        return $this->render('@App/queuelistouters.html.twig',array('list'=>$list));
    }

    /**
     * @Route("/queuelistitem/{status}", name="queue_list_by_status")
     */
    public function queuelistbystatusAction($status)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Queue');
        $list=$repository->findBy(array('status'=>$status),array('id'=>'asc'));
        switch ($status)
        {
            case '0':
                return $this->render('@App/queuelist.html.twig',array('list'=>$list));
            default:
                return $this->render('@App/queuelist.html.twig',array('list'=>$list));
        }

    }

    /**
     * @Route("/listitem/{status}", name="upload_list_by_status")
     */
    public function uploadlistbystatusAction($status)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Upload');
        $list=$repository->findBy(array('status'=>$status),array('id'=>'asc'));
        switch ($status)
        {
            case '0':
                return $this->render('@App/uploadlistloading.html.twig',array('list'=>$list));
            case '1':
                return $this->render('@App/uploadlistloaded.html.twig',array('list'=>$list));
            case '2':
                return $this->render('@App/uploadlistpublishing.html.twig',array('list'=>$list));
            case '3':
                return $this->render('@App/uploadlistpublished.html.twig',array('list'=>$list));
            case '4':
                return $this->render('@App/uploadlistrendering.html.twig',array('list'=>$list));
            case '5':
                return $this->render('@App/uploadlistrendered.html.twig',array('list'=>$list));
            default:
                return $this->render('@App/uploadlistloaded.html.twig',array('list'=>$list));
        }

    }

    /**
     * @Route("/deleteupload/{id}", name="delete_upload")
     */
    public function deleteuploadAction($id)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Upload');
        $upload=$repository->find($id);
        $manager->remove($upload);
        $manager->flush();
        return new JsonResponse();
    }


    /**
     * @Route("/cleanup/{id}", name="cleanup_upload")
     */
    public function cleanuploadAction($id)
    {

        $manager=$this->getDoctrine()->getManager();
        $producer=$this->container->get('old_sound_rabbit_mq.render_action_producer');
        UploadWorker::sendCleanUp($id,$manager,$producer);
        return new JsonResponse();
    }


    /**
     * @Route("/publish/{id}", name="publish_item")
     */
    public function publishitemAction($id)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Upload');
        $settrep=$manager->getRepository('AppBundle:Setting');
        $settings=$settrep->findAll();
        $settings=$settings[0];
        $item=$repository->find($id);
        if (sizeof($item->getSessions())==0)
        {
            $item=UploadWorker::splitUpload($item);
        }
        $producer=$this->container->get('old_sound_rabbit_mq.render_action_producer');
        UploadWorker::publishSessions($item,$this->getDoctrine()->getManager(),$producer,$settings);
        return new JsonResponse('ok');
    }

    /**
     * @Route("/sessions/{from}/{id}", name="session_list")
     */
    public function sessionitemAction($from,$id)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Upload');
        $item=$repository->find($id);
        if (sizeof($item->getSessions())==0)
        {
            UploadWorker::splitUpload($item);
            $item=$repository->find($id);
        }
        switch($from)
        {
            case 'publishing':
                $view='@App/sessioning.html.twig';
                break;
            case 'published':
                $view='@App/session.html.twig';
                break;
            case 'rendering':
                $view='@App/sessionring.html.twig';
                break;
            default:
                $view='@App/session.html.twig';
                break;
        }
        return $this->render($view,array(
            'queue'=>$item
        ));
    }

    /**
     * @Route("/frames/{type}/{id}", name="frames_list")
     */
    public function framesAction($type,$id)
    {
        $manager=$this->getDoctrine()->getManager();
        switch ($type)
        {
            case 'upload':
                $repository=$manager->getRepository('AppBundle:Upload');
                break;
            case 'session':
                $repository=$manager->getRepository('AppBundle:Session');
                break;
            default:
                $repository=$manager->getRepository('AppBundle:Session');
                break;
        }

        $item=$repository->find($id);
        return $this->render('@App/frames.html.twig',array(
            'frames'=>$item->getFrames()
        ));
    }

    /**
     * @Route("/getframe/{id}", name="get_frame_image")
     */
    public function getframeAction($id)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Frame');
        $item=$repository->find($id);
        if (!$item){return new JsonResponse('na');}
        $image=$item->getContent();
        $response = new StreamedResponse(function () use ($image) {
            echo stream_get_contents($image);
        });

        $response->headers->set('Content-Type', 'image/png');
        return $response;
    }

    /**
     * @Route("/render/session/{id}", name="render_session")
     */
    public function renderitemAction($id)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Session');
        $settrep=$manager->getRepository('AppBundle:Setting');
        $settings=$settrep->findAll();
        $settings=$settings[0];
        $item=$repository->find($id);
        $producer=$this->container->get('old_sound_rabbit_mq.render_action_producer');
        UploadWorker::renderSession($item,$this->getDoctrine()->getManager(),$producer,$settings);
        return new JsonResponse('ok');
    }

    /**
     * @Route("/render/upload/{id}", name="render_upload")
     */
    public function renderUploadAction($id)
    {
        $manager=$this->getDoctrine()->getManager();
        $repository=$manager->getRepository('AppBundle:Upload');
        $settrep=$manager->getRepository('AppBundle:Setting');
        $settings=$settrep->findAll();
        $settings=$settings[0];
        $item=$repository->find($id);
        $item->setStatus(4);
        $manager->persist($item);
        $manager->flush();
        $producer=$this->container->get('old_sound_rabbit_mq.render_action_producer');
        foreach($item->getSessions() as $session)
        {
            if ($session->getNodeName()!=null)
            {
                UploadWorker::renderSession($session,$manager,$producer,$settings);
            }
        }
        return new JsonResponse('ok');
    }
}

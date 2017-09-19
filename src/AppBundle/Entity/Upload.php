<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Upload
 *
 * @ORM\Table(name="upload")
 * @ORM\Entity
 */
class Upload
{
    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="FileName", type="string", length=100, nullable=true)
     */
    private $filename;

    /**
     * @var integer
     *
     * @ORM\Column(name="Status", type="integer", nullable=true)
     */
    private $status;


    /**
     * @var integer
     *
     * @ORM\Column(name="FileSize", type="integer", nullable=true)
     */
    private $filesize;

    /**
     * @var integer
     *
     * @ORM\Column(name="FrameStart", type="integer", nullable=true)
     */
    private $framestart;

    /**
     * @var integer
     *
     * @ORM\Column(name="FrameEnd", type="integer", nullable=true)
     */
    private $frameend;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return Upload
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Upload
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * Set filesize
     *
     * @param integer $filesize
     *
     * @return Upload
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * Set framestart
     *
     * @param integer $framestart
     *
     * @return Upload
     */
    public function setFramestart($framestart)
    {
        $this->framestart = $framestart;

        return $this;
    }

    /**
     * Get framestart
     *
     * @return integer
     */
    public function getFramestart()
    {
        return $this->framestart;
    }

    /**
     * Set frameend
     *
     * @param integer $frameend
     *
     * @return Upload
     */
    public function setFrameend($frameend)
    {
        $this->frameend = $frameend;

        return $this;
    }

    /**
     * Get frameend
     *
     * @return integer
     */
    public function getFrameend()
    {
        return $this->frameend;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $sessions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sessions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add session
     *
     * @param \AppBundle\Entity\Session $session
     *
     * @return Upload
     */
    public function addSession(\AppBundle\Entity\Session $session)
    {
        $this->sessions[] = $session;

        return $this;
    }

    /**
     * Remove session
     *
     * @param \AppBundle\Entity\Session $session
     */
    public function removeSession(\AppBundle\Entity\Session $session)
    {
        $this->sessions->removeElement($session);
    }

    /**
     * Get sessions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $chunks;


    /**
     * Add chunk
     *
     * @param \AppBundle\Entity\Uploadchunk $chunk
     *
     * @return Upload
     */
    public function addChunk(\AppBundle\Entity\Uploadchunk $chunk)
    {
        $this->chunks[] = $chunk;

        return $this;
    }

    /**
     * Remove chunk
     *
     * @param \AppBundle\Entity\Uploadchunk $chunk
     */
    public function removeChunk(\AppBundle\Entity\Uploadchunk $chunk)
    {
        $this->chunks->removeElement($chunk);
    }

    /**
     * Get chunks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChunks()
    {
        return $this->chunks;
    }
    /**
     * @var integer
     */
    private $chunkcount;


    /**
     * Set chunkcount
     *
     * @param integer $chunkcount
     *
     * @return Upload
     */
    public function setChunkcount($chunkcount)
    {
        $this->chunkcount = $chunkcount;

        return $this;
    }

    /**
     * Get chunkcount
     *
     * @return integer
     */
    public function getChunkcount()
    {
        return $this->chunkcount;
    }

    public function getFrameCount()
    {
        $count=0;
        foreach($this->getSessions() as $session)
        {
            $count=$count+sizeof($session->getFrames());
        }
        return $count;
    }

    public function getSessionCount()
    {
        return $this->sessions->count();
    }

    public function getSessionFilledCount()
    {
        $count=0;
        foreach($this->getSessions() as $session)
        {
            if ($session->getNodeName()!=null)
            {
                $count++;
            }
        }
        return $count;
    }

    public function hasEmptySessions()
    {
        $criteria=Criteria::create();
        $criteria->andWhere(Criteria::expr()->isNull('nodename'));
        $result= $this->sessions->matching($criteria);
        $resultarray=new ArrayCollection();
        if (sizeof($result)>0)
        {
            foreach($result as $item)
            {
                $resultarray->add($item);
                return true;
            }
        }
        return false;
    }

    public function hasOpenSessions()
    {
        $criteria=Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq('status',0));
        $result= $this->sessions->matching($criteria);
        $resultarray=new ArrayCollection();
        if (sizeof($result)>0)
        {
            foreach($result as $item)
            {
                $resultarray->add($item);
                return true;
            }
        }
        return false;
    }
    /**
     * @var integer
     */
    private $threads;


    /**
     * Set threads
     *
     * @param integer $threads
     *
     * @return Upload
     */
    public function setThreads($threads)
    {
        $this->threads = $threads;

        return $this;
    }

    /**
     * Get threads
     *
     * @return integer
     */
    public function getThreads()
    {
        return $this->threads;
    }

    public function framesTotal()
    {
        $count=0;
        foreach($this->getSessions() as $session)
        {
            $count=$count+(($session->getFrameEnd()+1)-$session->getFrameStart());
        }
        return $count;
    }

    public function framesGot()
    {
        $count=0;
        foreach($this->getSessions() as $session)
        {
            $count=$count+$session->getFrames()->count();
        }
        return $count;
    }

    public function getFrames()
    {
        $result=new ArrayCollection();
        foreach($this->getSessions() as $session)
        {
            if ($session->getFrames()->count()>0)
            {
                foreach($session->getFrames() as $frame)
                {
                    $result->add($frame);
                }
            }
        }
        return $result;
    }

}

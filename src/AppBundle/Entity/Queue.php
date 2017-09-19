<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Queue
 *
 * @ORM\Table(name="queue")
 * @ORM\Entity
 */
class Queue
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
     * @var integer
     *
     * @ORM\Column(name="OriginalId", type="bigint", nullable=true)
     */
    private $originalid;


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
     * Set originalid
     *
     * @param integer $originalid
     *
     * @return Queue
     */
    public function setOriginalid($originalid)
    {
        $this->originalid = $originalid;

        return $this;
    }

    /**
     * Get originalid
     *
     * @return integer
     */
    public function getOriginalid()
    {
        return $this->originalid;
    }


    /**
     * Set framestart
     *
     * @param integer $framestart
     *
     * @return Queue
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
     * @return Queue
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
     * @var string
     */
    private $sessionkey;


    /**
     * Set sessionkey
     *
     * @param string $sessionkey
     *
     * @return Queue
     */
    public function setSessionkey($sessionkey)
    {
        $this->sessionkey = $sessionkey;

        return $this;
    }

    /**
     * Get sessionkey
     *
     * @return string
     */
    public function getSessionkey()
    {
        return $this->sessionkey;
    }
    /**
     * @var string
     */
    private $sourcenode;


    /**
     * Set sourcenode
     *
     * @param string $sourcenode
     *
     * @return Queue
     */
    public function setSourcenode($sourcenode)
    {
        $this->sourcenode = $sourcenode;

        return $this;
    }

    /**
     * Get sourcenode
     *
     * @return string
     */
    public function getSourcenode()
    {
        return $this->sourcenode;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $frames;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->frames = new \Doctrine\Common\Collections\ArrayCollection();
        $this->chunks=new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add frame
     *
     * @param \AppBundle\Entity\Queueframe $frame
     *
     * @return Queue
     */
    public function addFrame(\AppBundle\Entity\Queueframe $frame)
    {
        $this->frames[] = $frame;

        return $this;
    }

    /**
     * Remove frame
     *
     * @param \AppBundle\Entity\Queueframe $frame
     */
    public function removeFrame(\AppBundle\Entity\Queueframe $frame)
    {
        $this->frames->removeElement($frame);
    }

    /**
     * Get frames
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFrames()
    {
        return $this->frames;
    }

    public function getFramebyNo($no)
    {
        $criteria=Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq('frameno',intval($no)));
        $criteria->orderBy(array('frameno'=>'asc'));
        $result= $this->frames->matching($criteria);
        $resultarray=new ArrayCollection();
        if (sizeof($result)>0)
        {
            foreach($result as $item)
            {
                $resultarray->add($item);
                return $resultarray[0];
            }
        }
        return null;
    }

    public function getChunkByNo($no)
    {
        $criteria=Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq('chunkno',intval($no)));
        $criteria->orderBy(array('chunkno'=>'asc'));
        $result= $this->chunks->matching($criteria);
        $resultarray=new ArrayCollection();
        if (sizeof($result)>0)
        {
            foreach($result as $item)
            {
                $resultarray->add($item);
                return $resultarray[0];
            }
        }
        return null;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $chunks;


    /**
     * Add chunk
     *
     * @param \AppBundle\Entity\Queuechunk $chunk
     *
     * @return Queue
     */
    public function addChunk(\AppBundle\Entity\Queuechunk $chunk)
    {
        $this->chunks[] = $chunk;

        return $this;
    }

    /**
     * Remove chunk
     *
     * @param \AppBundle\Entity\Queuechunk $chunk
     */
    public function removeChunk(\AppBundle\Entity\Queuechunk $chunk)
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
     * @return Queue
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
    /**
     * @var string
     */
    private $instancekey;


    /**
     * Set instancekey
     *
     * @param string $instancekey
     *
     * @return Queue
     */
    public function setInstancekey($instancekey)
    {
        $this->instancekey = $instancekey;

        return $this;
    }

    /**
     * Get instancekey
     *
     * @return string
     */
    public function getInstancekey()
    {
        return $this->instancekey;
    }
    /**
     * @var \DateTime
     */
    private $startdatetime;

    /**
     * @var \DateTime
     */
    private $enddatetime;


    /**
     * Set startdatetime
     *
     * @param \DateTime $startdatetime
     *
     * @return Queue
     */
    public function setStartdatetime($startdatetime)
    {
        $this->startdatetime = $startdatetime;

        return $this;
    }

    /**
     * Get startdatetime
     *
     * @return \DateTime
     */
    public function getStartdatetime()
    {
        return $this->startdatetime;
    }

    /**
     * Set enddatetime
     *
     * @param \DateTime $enddatetime
     *
     * @return Queue
     */
    public function setEnddatetime($enddatetime)
    {
        $this->enddatetime = $enddatetime;

        return $this;
    }

    /**
     * Get enddatetime
     *
     * @return \DateTime
     */
    public function getEnddatetime()
    {
        return $this->enddatetime;
    }
    /**
     * @var integer
     */
    private $status;


    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Queue
     */
    public function setStatus($status)
    {
        $this->status = $status;
        if ($this->status==1)
        {
            $this->setStartdatetime(new \DateTime('now'));
            $this->setEnddatetime(null);
        }
        if ($this->status==2)
        {
            $this->setEnddatetime(new \DateTime('now'));
        }

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
}

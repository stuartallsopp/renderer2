<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * Session
 *
 * @ORM\Table(name="session", indexes={@ORM\Index(name="ParentId", columns={"ParentId"})})
 * @ORM\Entity
 */
class Session
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
     * @var integer
     *
     * @ORM\Column(name="Status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="NodeName", type="string", length=50, nullable=true)
     */
    private $nodename;

    /**
     * @var \AppBundle\Entity\Upload
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Upload")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ParentId", referencedColumnName="Id")
     * })
     */
    private $parentid;



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
     * Set framestart
     *
     * @param integer $framestart
     *
     * @return Session
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
     * @return Session
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
     * Set status
     *
     * @param integer $status
     *
     * @return Session
     */
    public function setStatus($status)
    {
        $this->status = $status;
        if ($this->getStatus()==0)
        {
            $this->setStartdatetime(new \DateTime('now'));
            $this->setEnddatetime(null);
        }
        elseif($this->getStatus()==1)
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

    /**
     * Set nodename
     *
     * @param string $nodename
     *
     * @return Session
     */
    public function setNodename($nodename)
    {
        $this->nodename = $nodename;

        return $this;
    }

    /**
     * Get nodename
     *
     * @return string
     */
    public function getNodename()
    {
        return $this->nodename;
    }

    /**
     * Set parentid
     *
     * @param \AppBundle\Entity\Upload $parentid
     *
     * @return Session
     */
    public function setParentid(\AppBundle\Entity\Upload $parentid = null)
    {
        $this->parentid = $parentid;

        return $this;
    }

    /**
     * Get parentid
     *
     * @return \AppBundle\Entity\Upload
     */
    public function getParentid()
    {
        return $this->parentid;
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
     * @return Session
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $frames;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->frames = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add frame
     *
     * @param \AppBundle\Entity\Frame $frame
     *
     * @return Session
     */
    public function addFrame(\AppBundle\Entity\Frame $frame)
    {
        $this->frames[] = $frame;

        return $this;
    }

    /**
     * Remove frame
     *
     * @param \AppBundle\Entity\Frame $frame
     */
    public function removeFrame(\AppBundle\Entity\Frame $frame)
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
     * @return Session
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
     * @return Session
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

    public function getFramesTotal()
    {
        return ($this->getFrameend()+1)-$this->getFramestart();
    }

    public function getFramesGot()
    {
        return $this->getFrames()->count();
    }

    public function getStartDatetimer()
    {
        return date_format($this->getStartdatetime(),'Y-m-d H:i:s');
    }

    public function getEndDatetimer()
    {
        return date_format($this->getEnddatetime(),'Y-m-d H:i:s');
    }
}

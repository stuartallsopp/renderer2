<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Queueframe
 *
 * @ORM\Table(name="queueframe", indexes={@ORM\Index(name="QueueId", columns={"QueueId"})})
 * @ORM\Entity
 */
class Queueframe
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
     * @ORM\Column(name="FrameNo", type="integer", nullable=true)
     */
    private $frameno;

    /**
     * @var integer
     *
     * @ORM\Column(name="Status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="Content", type="blob", nullable=true)
     */
    private $content;

    /**
     * @var \AppBundle\Entity\Queue
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Queue")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="QueueId", referencedColumnName="Id")
     * })
     */
    private $queueid;



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
     * Set frameno
     *
     * @param integer $frameno
     *
     * @return Queueframe
     */
    public function setFrameno($frameno)
    {
        $this->frameno = $frameno;

        return $this;
    }

    /**
     * Get frameno
     *
     * @return integer
     */
    public function getFrameno()
    {
        return $this->frameno;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Queueframe
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
     * Set content
     *
     * @param string $content
     *
     * @return Queueframe
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set queueid
     *
     * @param \AppBundle\Entity\Queue $queueid
     *
     * @return Queueframe
     */
    public function setQueueid(\AppBundle\Entity\Queue $queueid = null)
    {
        $this->queueid = $queueid;

        return $this;
    }

    /**
     * Get queueid
     *
     * @return \AppBundle\Entity\Queue
     */
    public function getQueueid()
    {
        return $this->queueid;
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
     * @return Queueframe
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
     * @return Queueframe
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
    private $filesize;


    /**
     * Set filesize
     *
     * @param integer $filesize
     *
     * @return Queueframe
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
}

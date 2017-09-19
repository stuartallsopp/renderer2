<?php

namespace AppBundle\Entity;

/**
 * Queuechunk
 */
class Queuechunk
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $chunkno;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $contentbase64;

    /**
     * @var \AppBundle\Entity\Queue
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
     * Set chunkno
     *
     * @param integer $chunkno
     *
     * @return Queuechunk
     */
    public function setChunkno($chunkno)
    {
        $this->chunkno = $chunkno;

        return $this;
    }

    /**
     * Get chunkno
     *
     * @return integer
     */
    public function getChunkno()
    {
        return $this->chunkno;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Queuechunk
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
     * Set contentbase64
     *
     * @param string $contentbase64
     *
     * @return Queuechunk
     */
    public function setContentbase64($contentbase64)
    {
        $this->contentbase64 = $contentbase64;

        return $this;
    }

    /**
     * Get contentbase64
     *
     * @return string
     */
    public function getContentbase64()
    {
        return $this->contentbase64;
    }

    /**
     * Set queueid
     *
     * @param \AppBundle\Entity\Queue $queueid
     *
     * @return Queuechunk
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
     * @var string
     */
    private $instancekey;


    /**
     * Set instancekey
     *
     * @param string $instancekey
     *
     * @return Queuechunk
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
}

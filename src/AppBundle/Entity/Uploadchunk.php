<?php

namespace AppBundle\Entity;

/**
 * Uploadchunk
 */
class Uploadchunk
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
     * @var \AppBundle\Entity\Upload
     */
    private $uploadid;


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
     * @return Uploadchunk
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
     * @return Uploadchunk
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
     * @return Uploadchunk
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
     * Set uploadid
     *
     * @param \AppBundle\Entity\Upload $uploadid
     *
     * @return Uploadchunk
     */
    public function setUploadid(\AppBundle\Entity\Upload $uploadid = null)
    {
        $this->uploadid = $uploadid;

        return $this;
    }

    /**
     * Get uploadid
     *
     * @return \AppBundle\Entity\Upload
     */
    public function getUploadid()
    {
        return $this->uploadid;
    }
}

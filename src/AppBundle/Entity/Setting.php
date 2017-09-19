<?php

namespace AppBundle\Entity;

/**
 * Setting
 */
class Setting
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nodename;


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
     * Set nodename
     *
     * @param string $nodename
     *
     * @return Setting
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
     * @var string
     */
    private $blendpath;


    /**
     * Set blendpath
     *
     * @param string $blendpath
     *
     * @return Setting
     */
    public function setBlendpath($blendpath)
    {
        $this->blendpath = $blendpath;

        return $this;
    }

    /**
     * Get blendpath
     *
     * @return string
     */
    public function getBlendpath()
    {
        return $this->blendpath;
    }
    /**
     * @var string
     */
    private $applicationpath;


    /**
     * Set applicationpath
     *
     * @param string $applicationpath
     *
     * @return Setting
     */
    public function setApplicationpath($applicationpath)
    {
        $this->applicationpath = $applicationpath;

        return $this;
    }

    /**
     * Get applicationpath
     *
     * @return string
     */
    public function getApplicationpath()
    {
        return $this->applicationpath;
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Frame
 *
 * @ORM\Table(name="frame", indexes={@ORM\Index(name="SessionId", columns={"SessionId"})})
 * @ORM\Entity
 */
class Frame
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
     * @var string
     *
     * @ORM\Column(name="Content", type="blob", nullable=true)
     */
    private $content;

    /**
     * @var \AppBundle\Entity\Session
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Session")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="SessionId", referencedColumnName="Id")
     * })
     */
    private $sessionid;



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
     * @return Frame
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
     * Set content
     *
     * @param string $content
     *
     * @return Frame
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
     * Set sessionid
     *
     * @param \AppBundle\Entity\Session $sessionid
     *
     * @return Frame
     */
    public function setSessionid(\AppBundle\Entity\Session $sessionid = null)
    {
        $this->sessionid = $sessionid;

        return $this;
    }

    /**
     * Get sessionid
     *
     * @return \AppBundle\Entity\Session
     */
    public function getSessionid()
    {
        return $this->sessionid;
    }
}

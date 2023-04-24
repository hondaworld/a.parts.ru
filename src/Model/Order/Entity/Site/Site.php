<?php

namespace App\Model\Order\Entity\Site;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Order\Entity\Order\Order;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SiteRepository::class)
 * @ORM\Table(name="sites")
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="siteID")
     */
    private $siteID;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="boolean", name="isSklad")
     */
    private $isSklad;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $norma_price;

    /**
     * @var Order[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="site")
     */
    private $orders;

    /**
     * @var Creater[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="sites")
     * @ORM\JoinTable(name="linkSiteCreater",
     *      joinColumns={@ORM\JoinColumn(name="siteID", referencedColumnName="siteID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="createrID", referencedColumnName="createrID")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $creaters;

    /**
     * @var AutoMarka[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Auto\Entity\Marka\AutoMarka", inversedBy="sites")
     * @ORM\JoinTable(name="linkSiteMarka",
     *      joinColumns={@ORM\JoinColumn(name="siteID", referencedColumnName="siteID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="auto_markaID", referencedColumnName="auto_markaID")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $auto_marka;

    /**
     * @var ClientTicket[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Ticket\Entity\ClientTicket\ClientTicket", mappedBy="site")
     */
    private $tickets;

    public function __construct(string $name_short, string $name, string $url, bool $isSklad, ?string $norma_price)
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->url = $url;
        $this->isSklad = $isSklad;
        $this->norma_price = $norma_price ?: 0;
        $this->creaters = new ArrayCollection();
        $this->auto_marka = new ArrayCollection();
    }

    public function update(string $name_short, string $name, string $url, bool $isSklad, ?string $norma_price)
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->url = $url;
        $this->isSklad = $isSklad;
        $this->norma_price = $norma_price ?: 0;
    }

    public function getId(): ?int
    {
        return $this->siteID;
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function isSklad(): ?bool
    {
        return $this->isSklad;
    }

    public function getNormaPrice(): ?string
    {
        return $this->norma_price;
    }

    /**
     * @return Order[]|ArrayCollection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return Creater[]|ArrayCollection
     */
    public function getCreaters()
    {
        return $this->creaters->toArray();
    }

    public function clearCreaters(): void
    {
        $this->creaters->clear();
    }

    /**
     * @param Creater $creater
     */
    public function assignCreater(Creater $creater): void
    {
        $this->creaters->add($creater);
    }

    /**
     * @return AutoMarka[]|ArrayCollection
     */
    public function getAutoMarka()
    {
        return $this->auto_marka->toArray();
    }

    public function clearAutoMarka(): void
    {
        $this->auto_marka->clear();
    }

    /**
     * @param AutoMarka $autoMarka
     */
    public function assignAutoMarka(AutoMarka $autoMarka): void
    {
        $this->auto_marka->add($autoMarka);
    }


}

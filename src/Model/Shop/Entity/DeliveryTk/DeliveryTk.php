<?php

namespace App\Model\Shop\Entity\DeliveryTk;

use App\Model\Expense\Entity\Shipping\Shipping;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeliveryTkRepository::class)
 * @ORM\Table(name="delivery_tk")
 */
class DeliveryTk
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="delivery_tkID")
     */
    private $delivery_tkID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $http;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sms_text;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @var Shipping[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Shipping\Shipping", mappedBy="delivery_tk")
     */
    private $shippings;

    public function __construct(string $name, ?string $http, ?string $sms_text)
    {
        $this->name = $name;
        $this->http = $http ?: '';
        $this->sms_text = $sms_text ?: '';
    }

    public function update(string $name, ?string $http, ?string $sms_text)
    {
        $this->name = $name;
        $this->http = $http ?: '';
        $this->sms_text = $sms_text ?: '';
    }

    public function getId(): ?int
    {
        return $this->delivery_tkID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getHttp(): ?string
    {
        return $this->http;
    }

    public function getSmsText(): ?string
    {
        return $this->sms_text;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }
}

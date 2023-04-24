<?php

namespace App\Model\Expense\Entity\ShippingPlace;

use App\Model\Expense\Entity\Shipping\Shipping;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShippingPlaceRepository::class)
 * @ORM\Table(name="shipping_places")
 */
class ShippingPlace
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="shipping_placeID")
     */
    private $shipping_placeID;

    /**
     * @var Shipping
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\Shipping\Shipping", inversedBy="places")
     * @ORM\JoinColumn(name="shippingID", referencedColumnName="shippingID", nullable=false)
     */
    private $shipping;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="integer")
     */
    private $length;

    /**
     * @ORM\Column(type="integer")
     */
    private $width;

    /**
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @ORM\Column(type="koef", precision=8, scale=4)
     */
    private $weight;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo1 = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo2 = '';

    public function __construct(int $number, int $length, int $width, int $height, string $weight)
    {
        $this->number = $number;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->weight = $weight;
    }

    public function update(int $number, int $length, int $width, int $height, string $weight)
    {
        $this->number = $number;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->weight = $weight;
    }

    public function updatePhoto1(string $photo1)
    {
        $this->photo1 = $photo1;
    }

    public function updatePhoto2(string $photo2)
    {
        $this->photo2 = $photo2;
    }

    public function updateShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    public function removePhoto1(): void
    {
        $this->photo1 = '';
    }

    public function removePhoto2(): void
    {
        $this->photo2 = '';
    }

    public function getId(): ?int
    {
        return $this->shipping_placeID;
    }

    public function getShipping(): Shipping
    {
        return $this->shipping;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWeight(): string
    {
        return $this->weight;
    }

    public function getPhoto1(): string
    {
        return $this->photo1;
    }

    public function getPhoto2(): string
    {
        return $this->photo2;
    }
}

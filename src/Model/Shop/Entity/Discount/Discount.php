<?php

namespace App\Model\Shop\Entity\Discount;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DiscountRepository::class)
 * @ORM\Table(name="discounts")
 */
class Discount
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="discountID")
     */
    private $discountID;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $summ;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $discount_spare;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $discount_service;

    public function __construct(string $summ, string $discount_spare, string $discount_service)
    {
        $this->summ = $summ;
        $this->discount_spare = $discount_spare;
        $this->discount_service = $discount_service;
    }

    public function update(string $summ, string $discount_spare, string $discount_service)
    {
        $this->summ = $summ;
        $this->discount_spare = $discount_spare;
        $this->discount_service = $discount_service;
    }

    public function getId(): ?int
    {
        return $this->discountID;
    }

    public function getSumm(): float
    {
        return $this->summ;
    }

    public function getDiscountSpare(): float
    {
        return $this->discount_spare;
    }

    public function getDiscountService(): float
    {
        return $this->discount_service;
    }
}

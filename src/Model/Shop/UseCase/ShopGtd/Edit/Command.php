<?php

namespace App\Model\Shop\UseCase\ShopGtd\Edit;

use App\Model\Shop\Entity\Gtd\ShopGtd;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $shop_gtdID;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\AtLeastOneOf({
     *     @Assert\Regex(pattern="#^([0-9]){8}(/){1}([0-9]){6}(/){1}(.){7}$#", message="00000000/000000/*******"),
     *     @Assert\Regex(pattern="#^([0-9]){8}(/){1}([0-9]){6}(/){1}([0-9]){7}(/){1}([0-9]){1,3}$#", message="00000000/000000/0000000/000"),
     *     @Assert\Regex(pattern="#^([\-]){10}$#", message="----------"),
     * }, message="Допустимые значения")
     */
    public $name;

    public function __construct(int $shop_gtdID) {
        $this->shop_gtdID = $shop_gtdID;
    }

    public static function fromDocument(ShopGtd $shopGtd): self
    {
        $command = new self($shopGtd->getId());
        $command->name = $shopGtd->getName()->getValue();
        return $command;
    }
}

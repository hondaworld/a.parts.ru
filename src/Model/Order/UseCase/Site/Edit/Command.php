<?php

namespace App\Model\Order\UseCase\Site\Edit;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Order\Entity\Site\Site;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $siteID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $url;

    public $isSklad;

    /**
     * @var string
     */
    public $norma_price;

    /**
     * @var array
     */
    public $creaters;

    /**
     * @var array
     */
    public $auto_marka;

    public function __construct(int $siteID)
    {
        $this->siteID = $siteID;
    }

    public static function fromEntity(Site $site): self
    {
        $command = new self($site->getId());
        $command->name_short = $site->getNameShort();
        $command->name = $site->getName();
        $command->url = $site->getUrl();
        $command->isSklad = $site->isSklad();
        $command->norma_price = $site->getNormaPrice();
        $command->creaters = array_map(function (Creater $creater): int {
            return $creater->getId();
        }, $site->getCreaters());
        $command->auto_marka = array_map(function (AutoMarka $marka): int {
            return $marka->getId();
        }, $site->getAutoMarka());
        return $command;
    }
}

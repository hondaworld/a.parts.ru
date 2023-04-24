<?php

namespace App\Model\Card\UseCase\Group\Create;

use App\Model\Card\Entity\Category\ZapCategory;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $zapCategory;

    public function __construct(ZapCategory $zapCategory)
    {
        $this->zapCategory = $zapCategory;
    }
}

<?php

namespace App\Model\User\UseCase\Template\Create;

use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="50",
     *     minMessage="Тема должна быть не больше 50 символов"
     * )
     */
    public $subject;

    /**
     * @Assert\NotBlank()
     */
    public $text;

    /**
     * @Assert\NotBlank()
     */
    public $templateGroupID;

    public function __construct(TemplateGroup $templateGroup)
    {
        $this->templateGroupID = $templateGroup->getId();
    }
}

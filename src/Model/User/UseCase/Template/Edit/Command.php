<?php

namespace App\Model\User\UseCase\Template\Edit;

use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $templateID;
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

    public function __construct(int $templateID)
    {
        $this->templateID = $templateID;
    }

    public static function fromEntity(Template $template): self
    {
        $command = new self($template->getId());
        $command->templateGroupID = $template->getTemplateGroup()->getId();
        $command->name = $template->getName();
        $command->subject = $template->getSubject();
        $command->text = $template->getText();
        return $command;
    }
}

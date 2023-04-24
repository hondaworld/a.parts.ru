<?php

namespace App\Model\User\Entity\Template;

use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TemplateRepository::class)
 * @ORM\Table(name="templates")
 */
class Template
{
    public const PRICE_SEND = 3;
    public const PRICE_SUMMARY_SEND = 20;
    public const USER_DOCUMENTS = 7;
    public const EMAIL_SHIPPING = 2;
    public const SMS_SHIPPING = 9;
    public const EMAIL_SCHET_LINK = 1;
    public const ORDER_SENT = 6;
    public const EMAIL_CREDIT_CARD_LINK = 40;
    public const SMS_ORDER_CONFIRM = 16;
    public const EMAIL_ORDER_CONFIRM = 17;
    public const SMS_PAY_CONFIRM = 18;
    public const EMAIL_PAY_CONFIRM = 19;
    public const SMS_SCHET_PAY_URL = 41;
    public const INCOME_STATUSES = 4;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="templateID")
     */
    private $templateID;

    /**
     * @var TemplateGroup
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\TemplateGroup\TemplateGroup", inversedBy="templates")
     * @ORM\JoinColumn(name="templateGroupID", referencedColumnName="templateGroupID", nullable=false)
     */
    private $template_group;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $subject;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    public function __construct(TemplateGroup $templateGroup, string $name, string $subject, string $text)
    {
        $this->template_group = $templateGroup;
        $this->name = $name;
        $this->subject = $subject;
        $this->text = $text;
    }

    public function update(TemplateGroup $templateGroup, string $name, string $subject, string $text)
    {
        $this->template_group = $templateGroup;
        $this->name = $name;
        $this->subject = $subject;
        $this->text = $text;
    }

    public function getId(): int
    {
        return $this->templateID;
    }

    public function getTemplateGroup(): TemplateGroup
    {
        return $this->template_group;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getText($params = []): string
    {
        $text = $this->text;
        foreach ($params as $param => $value) {
            $text = str_replace("{" . $param . "}", $value, $text);
        }
        return $text;
    }
}

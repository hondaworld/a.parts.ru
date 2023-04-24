<?php

namespace App\Model\User\Entity\TemplateGroup;

use App\Model\User\Entity\Template\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TemplateGroupRepository::class)
 * @ORM\Table(name="templateGroups")
 */
class TemplateGroup
{
    public const PAY = 2;
    public const WAREHOUSE = 4;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="templateGroupID")
     */
    private $templateGroupID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var Template[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\Template\Template", mappedBy="template_group")
     */
    private $templates;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->templateGroupID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNoneDelete(): bool
    {
        return $this->noneDelete;
    }

    /**
     * @return Template[]|ArrayCollection
     */
    public function getTemplates()
    {
        return $this->templates->toArray();
    }

}

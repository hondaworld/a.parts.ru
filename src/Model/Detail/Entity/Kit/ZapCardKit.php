<?php

namespace App\Model\Detail\Entity\Kit;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumber;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardKitRepository::class)
 * @ORM\Table(name="zapCardKits")
 */
class ZapCardKit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var AutoModel
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Model\AutoModel", inversedBy="kits")
     * @ORM\JoinColumn(name="auto_modelID", referencedColumnName="auto_modelID", nullable=false)
     */
    private $auto_model;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @var ZapCardKitNumber[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\KitNumber\ZapCardKitNumber", mappedBy="kit", orphanRemoval=true)
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $numbers;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    public function __construct(AutoModel $auto_model, string $name, int $sort)
    {
        $this->auto_model = $auto_model;
        $this->name = $name;
        $this->sort = $sort;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAutoModel(): AutoModel
    {
        return $this->auto_model;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    /**
     * @return ZapCardKitNumber[]|ArrayCollection
     */
    public function getNumbers()
    {
        return $this->numbers->toArray();
    }

    public function changeSort(int $sort): void
    {
        $this->sort = $sort;
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

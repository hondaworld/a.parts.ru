<?php

namespace App\Model\Auto\Entity\Auto;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=AutoRepository::class)
 * @ORM\Table(name="autos")
 */
class Auto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="autoID")
     */
    private $autoID;

    /**
     * @var AutoModel
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Model\AutoModel", inversedBy="autos")
     * @ORM\JoinColumn(name="auto_modelID", referencedColumnName="auto_modelID", nullable=true)
     */
    private $model;

    /**
     * @var AutoNumber
     * @ORM\Column(type="auto_number", length=20)
     */
    private $number;

    /**
     * @var Vin
     * @ORM\Column(type="vin", length=20)
     */
    private $vin;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var User[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\User\Entity\User\User", inversedBy="autos")
     * @ORM\JoinTable(name="linkUserAuto",
     *      joinColumns={@ORM\JoinColumn(name="autoID", referencedColumnName="autoID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userID", referencedColumnName="userID")}
     * )
     */
    private $users;


    public function __construct(AutoModel $model, Vin $vin, AutoNumber $number, ?int $year)
    {
        $this->model = $model;
        $this->vin = $vin;
        $this->number = $number;
        $this->year = intval($year) ?: 0;
        $this->users = new ArrayCollection();
    }

    public function update(AutoModel $model, Vin $vin, AutoNumber $number, ?int $year)
    {
        $this->model = $model;
        $this->vin = $vin;
        $this->number = $number;
        $this->year = intval($year) ?: 0;
    }

    public function getId(): ?int
    {
        return $this->autoID;
    }

    public function getModel(): ?AutoModel
    {
        return $this->model;
    }

    public function getNumber(): AutoNumber
    {
        return $this->number;
    }

    public function getVin(): Vin
    {
        return $this->vin;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getUsers()
    {
        return $this->users->toArray();
    }

    public function assignUser(User $user): void
    {
        $this->users->add($user);
    }

}

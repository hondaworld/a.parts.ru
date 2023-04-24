<?php

namespace App\Model\Auto\Entity\Moto;

use App\Model\Auto\Entity\Auto\AutoNumber;
use App\Model\Auto\Entity\Auto\Vin;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MotoRepository::class)
 * @ORM\Table(name="motos")
 */
class Moto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="motoID")
     */
    private $motoID;

    /**
     * @var MotoModel
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\MotoModel\MotoModel", inversedBy="motos")
     * @ORM\JoinColumn(name="moto_modelID", referencedColumnName="moto_modelID", nullable=true)
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
     * @ORM\ManyToMany(targetEntity="App\Model\User\Entity\User\User", inversedBy="motos")
     * @ORM\JoinTable(name="linkUserMoto",
     *      joinColumns={@ORM\JoinColumn(name="motoID", referencedColumnName="motoID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userID", referencedColumnName="userID")}
     * )
     */
    private $users;

    public function __construct(MotoModel $model, Vin $vin, AutoNumber $number, ?int $year)
    {
        $this->model = $model;
        $this->number = $number;
        $this->vin = $vin;
        $this->year = intval($year) ?: 0;
        $this->users = new ArrayCollection();
    }

    public function update(MotoModel $model, Vin $vin, AutoNumber $number, ?int $year)
    {
        $this->model = $model;
        $this->number = $number;
        $this->vin = $vin;
        $this->year = intval($year) ?: 0;
    }

    public function getId(): ?int
    {
        return $this->motoID;
    }

    public function getModel(): ?MotoModel
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

    public function getYear(): ?int
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

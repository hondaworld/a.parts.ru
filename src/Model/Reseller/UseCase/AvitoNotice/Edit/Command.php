<?php

namespace App\Model\Reseller\UseCase\AvitoNotice\Edit;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Reseller\Entity\Avito\AvitoNotice;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $avito_id;

    /**
     * @var string
     */
    public $contact_phone;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="255",
     *     maxMessage="Адрес должен быть не больше 255 символов"
     * )
     */
    public $address;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="50",
     *     maxMessage="Наименование должно быть не больше 50 символов"
     * )
     */
    public $title;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="7500",
     *     maxMessage="Описание должно быть не больше 7500 символов"
     * )
     */
    public $description;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $type_id;

    /**
     * @var string
     */
    public $image_urls;

    /**
     * @var string
     */
    public $make;

    /**
     * @var string
     */
    public $model;

    /**
     * @var string
     */
    public $generation;

    /**
     * @var string
     */
    public $modification;

    public $models = [];

    public $generations = [];

    public $modifications = [];

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromAvitoNotice(AvitoNotice $avitoNotice): self
    {
        $command = new self($avitoNotice->getId());
        $command->avito_id = $avitoNotice->getAvitoId();
        $command->contact_phone = $avitoNotice->getContactPhone();
        $command->address = $avitoNotice->getAddress();
        $command->title = $avitoNotice->getTitle();
        $command->description = $avitoNotice->getDescription();
        $command->type_id = $avitoNotice->getTypeId();
        $command->image_urls = $avitoNotice->getImageUrls();
        $command->make = $avitoNotice->getMake();
        $command->model = $avitoNotice->getModel();
        $command->generation = $avitoNotice->getGeneration();
        $command->modification = $avitoNotice->getModification();
        $command->image_urls = implode("\n", explode(" | ", $avitoNotice->getImageUrls()));
        return $command;
    }
}

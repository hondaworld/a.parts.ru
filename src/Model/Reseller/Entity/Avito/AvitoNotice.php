<?php

namespace App\Model\Reseller\Entity\Avito;

use App\Model\Card\Entity\Card\ZapCard;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AvitoNoticeRepository::class)
 * @ORM\Table(name="avito_notices")
 */
class AvitoNotice
{
    public const CONTACT_PHONE = '8 993 590-10-10';
    public const ADDRESS = 'Москва, 1-й Дорожный пр., 5';
    public const TYPES = [
        '11-618' => 'Автосвет',
        '19-2855' => 'Автомобиль на запчасти',
        '11-619' => 'Аккумуляторы',
        '16-827' => 'Двигатель / Блок цилиндров, головка, картер',
        '16-828' => 'Двигатель / Вакуумная система',
        '16-829' => 'Двигатель / Генераторы, стартеры',
        '16-830' => 'Двигатель / Двигатель в сборе',
        '16-831' => 'Двигатель / Катушка зажигания, свечи, электрика',
        '16-832' => 'Двигатель / Клапанная крышка',
        '16-833' => 'Двигатель / Коленвал, маховик',
        '16-834' => 'Двигатель / Коллекторы',
        '16-835' => 'Двигатель / Крепление двигателя',
        '16-836' => 'Двигатель / Масляный насос, система смазки',
        '16-837' => 'Двигатель / Патрубки вентиляции',
        '16-838' => 'Двигатель / Поршни, шатуны, кольца',
        '16-839' => 'Двигатель / Приводные ремни, натяжители',
        '16-840' => 'Двигатель / Прокладки и ремкомплекты',
        '16-841' => 'Двигатель / Ремни, цепи, элементы ГРМ',
        '16-842' => 'Двигатель / Турбины, компрессоры',
        '16-843' => 'Двигатель / Электродвигатели и компоненты',
        '11-621' => 'Запчасти для ТО',
        '16-805' => 'Кузов / Балки, лонжероны',
        '16-806' => 'Кузов / Бамперы',
        '16-807' => 'Кузов / Брызговики',
        '16-808' => 'Кузов / Двери',
        '16-809' => 'Кузов / Заглушки',
        '16-810' => 'Кузов / Замки',
        '16-811' => 'Кузов / Защита',
        '16-812' => 'Кузов / Зеркала',
        '16-813' => 'Кузов / Кабина',
        '16-814' => 'Кузов / Капот',
        '16-815' => 'Кузов / Крепления',
        '16-816' => 'Кузов / Крылья',
        '16-817' => 'Кузов / Крыша',
        '16-818' => 'Кузов / Крышка, дверь багажника',
        '16-819' => 'Кузов / Кузов по частям',
        '16-820' => 'Кузов / Кузов целиком',
        '16-821' => 'Кузов / Лючок бензобака',
        '16-822' => 'Кузов / Молдинги, накладки',
        '16-823' => 'Кузов / Пороги',
        '16-824' => 'Кузов / Рама',
        '16-825' => 'Кузов / Решетка радиатора',
        '16-826' => 'Кузов / Стойка кузова',
        '11-623' => 'Подвеска',
        '11-624' => 'Рулевое управление',
        '11-625' => 'Салон',
        '16-521' => 'Система охлаждения',
        '11-626' => 'Стекла',
        '11-627' => 'Топливная и выхлопная системы',
        '11-628' => 'Тормозная система',
        '11-629' => 'Трансмиссия и привод',
        '11-630' => 'Электрооборудование'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="avito_notices")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @ORM\Column(type="integer")
     */
    private $avito_id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $contact_phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description = '';

    /**
     * @ORM\Column(type="text")
     */
    private $image_urls = '';

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $type_id = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $oem;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $make = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $model = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $generation = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $modification = '';

    public function __construct(ZapCard $zapCard, string $contact_phone, string $address, string $title, string $brand, string $oem, string $image_urls)
    {
        $this->zapCard = $zapCard;
        $this->contact_phone = $contact_phone;
        $this->address = $address;
        $this->title = $title;
        $this->brand = $brand;
        $this->oem = $oem;
        $this->image_urls = $image_urls;
    }

    public function update(?int $avito_id, ?string $contact_phone, string $address, string $title, string $description, string $type_id, ?string $image_urls, ?int $make, ?int $model, ?int $generation, ?int $modification)
    {
        $this->avito_id = $avito_id;
        $this->contact_phone = $contact_phone ?: '';
        $this->address = $address;
        $this->title = $title;
        $this->description = $description;
        $this->type_id = $type_id;
        $this->image_urls = $image_urls ?: '';
        $this->make = $make;
        $this->model = $model;
        $this->generation = $generation;
        $this->modification = $modification;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getAvitoId(): ?int
    {
        return $this->avito_id;
    }

    public function getContactPhone(): string
    {
        return $this->contact_phone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImageUrls(): string
    {
        return $this->image_urls;
    }

    public function getTypeId(): string
    {
        return $this->type_id;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getOem(): string
    {
        return $this->oem;
    }

    public function getMake(): ?int
    {
        return $this->make;
    }

    public function getModel(): ?int
    {
        return $this->model;
    }

    public function getGeneration(): ?int
    {
        return $this->generation;
    }

    public function getModification(): ?int
    {
        return $this->modification;
    }
}

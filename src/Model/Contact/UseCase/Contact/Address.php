<?php


namespace App\Model\Contact\UseCase\Contact;
use Symfony\Component\Validator\Constraints as Assert;


class Address
{

    /**
     * @var Town
     * @Assert\Valid()
     */
    public $town;

    /**
     * @Assert\Length(
     *     max="6"
     * )
     */
    public $zip;

    public $street;

    /**
     * @Assert\Length(
     *     max="15"
     * )
     */
    public $house;

    /**
     * @Assert\Length(
     *     max="15"
     * )
     */
    public $str;

    /**
     * @Assert\Length(
     *     max="15"
     * )
     */
    public $kv;

    public function __construct(Town $town, string $zip = '', string $street = '', string $house = '', string $str = '', string $kv = '')
    {
        $this->town = $town;
        $this->zip = $zip;
        $this->street = $street;
        $this->house = $house;
        $this->str = $str;
        $this->kv = $kv;
    }
}
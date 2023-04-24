<?php


namespace App\ReadModel;


class DropDownList
{
    public string $id;

    public string $name;

    public array $item;

    public function __construct(string $id, string $name, array $item = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->item = $item;
    }
}
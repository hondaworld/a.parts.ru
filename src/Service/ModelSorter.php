<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\Request;

class ModelSorter
{

    /**
     * @var Request
     */
    private Request $request;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    public function getNewSort(int $id, int $oldSort): int
    {
        $newSort = $oldSort;

        $idStart = $this->request->get('idStart');
        $idFinish = $this->request->get('idFinish');
        $direction = $this->request->get('direction');

        if ($idStart && $idFinish) {
            $sortStart = array_flip($idStart)[$id];
            $sortFinish = array_flip($idFinish)[$id];
            $additional = $direction == 'desc' ? -1 : 1;

            $newSort = $oldSort + ($sortFinish - $sortStart) * $additional;
        }

        return $newSort;
    }
}
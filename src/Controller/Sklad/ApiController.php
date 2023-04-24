<?php


namespace App\Controller\Sklad;


use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Sklad\ZapCardFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $fetcher;

    public function __construct(ZapCardFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * @Route("/api/skladZapCardNumbers/{id}", name="api_skladZapCardNumbers")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @return Response
     */
    public function number(ZapSklad $zapSklad, Request $request): Response
    {
        $str = $request->query->get('number');

        $numbers = [];
        if (mb_strlen($str, 'UTF-8') >= 3) {
            $numbers = $this->fetcher->findUniqueNumbers($zapSklad, $str);
        }

        return $this->json($numbers);
    }
}
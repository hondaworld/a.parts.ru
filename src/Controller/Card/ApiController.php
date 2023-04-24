<?php


namespace App\Controller\Card;


use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Beznal\BeznalFetcher;
use App\ReadModel\Card\ZapCardFetcher;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\User\UserFetcher;
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
     * @Route("/api/zapCardNumbers", name="api_zapCardNumbers")
     * @param Request $request
     * @return Response
     */
    public function number(Request $request): Response
    {
        $str = $request->query->get('number');

        $numbers = [];
        if (mb_strlen($str, 'UTF-8') >= 3) {
            $numbers = $this->fetcher->findUniqueNumbers($str);
        }

        return $this->json($numbers);
    }
}
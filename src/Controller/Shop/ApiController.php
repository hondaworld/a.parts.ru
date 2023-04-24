<?php


namespace App\Controller\Shop;


use App\ReadModel\Shop\ShopGtdFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/gtd", name="api_gtd")
     * @param Request $request
     * @param ShopGtdFetcher $shopGtdFetcher
     * @return Response
     */
    public function gtd(Request $request, ShopGtdFetcher $shopGtdFetcher): Response
    {
        $str = $request->query->get('number');

        $gtd = [];
        if (mb_strlen($str, 'UTF-8') >= 6) {
            $gtd = $shopGtdFetcher->findGtd($str);
        }

        return $this->json($gtd);
    }
}
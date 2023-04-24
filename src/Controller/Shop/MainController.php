<?php


namespace App\Controller\Shop;


use App\Model\Card\Entity\Main\Main;
use App\Model\Card\Entity\Main\MainRepository;
use App\ReadModel\Provider\Filter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/main", name="main")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/sms/balance", name=".sms.balance")
     * @param MainRepository $mainRepository
     * @return Response
     */
    public function smsBalance(MainRepository $mainRepository): Response
    {
        $main = $mainRepository->get(Main::DEFAULT_ID);
        return $this->json(number_format($main->getSmsRuBalance(), 2, ',', ' ') . ' р.');
    }
}
<?php


namespace App\Controller\Beznal;


use App\ReadModel\Beznal\BankFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $fetcher;

    public function __construct(BankFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * @Route("/api/banks", name="api_banks")
     * @param Request $request
     * @return Response
     */
    public function bank(Request $request): Response
    {
        $str = $request->query->get('name');

        $data = [];
        $banks = [];
        if (mb_strlen($str, 'UTF-8') >= 3) {
            $banks = $this->fetcher->findBanksByBik($str);
        }

        if ($banks) {
            foreach ($banks as $bank) {
                $data[] = ['id' => $bank->bankID, 'name' => $bank->getBankFullName()];
            }
        }

        return $this->json($data);
    }
}
<?php


namespace App\Controller\Contact;


use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\Contact\TownFetcher;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $fetcher;

    public function __construct(TownFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * @Route("/api/towns", name="api_towns")
     * @param Request $request
     * @return Response
     */
    public function town(Request $request): Response
    {
        $str = $request->query->get('name');

        $data = [];
        $towns = [];
        if (mb_strlen($str, 'UTF-8') >= 3) {
            $towns = $this->fetcher->findTownsByName($str);
        }

        if ($towns) {
            $dataTowns = [];
            $dataRegions = [];
            foreach ($towns as $town) {
                if ($town->typeID == 1)
                    $dataTowns[] = ['id' => $town->townID, 'name' => $town->getTownFullName()];
                else
                    $dataRegions[] = ['id' => $town->townID, 'name' => $town->getTownFullName()];
            }
            $data = array_merge($dataTowns, $dataRegions);
        }

        return $this->json($data);
    }

    /**
     * @Route("/api/provider/contact", name="api.provider.contact")
     * @param ProviderRepository $providerRepository
     * @return Response
     */
    public function providerContact(Request $request, ProviderRepository $providerRepository, ContactFetcher $contactFetcher): Response
    {
        $providerID = $request->query->get('id');

        if ($providerID == "") {
            $contacts = [];
        } else {

            try {
                $provider = $providerRepository->get($providerID);
                $contacts = $contactFetcher->assocAllByUser($provider->getUser());
            } catch (DomainException $e) {
                $contacts = [];
            }
        }

        return $this->render('app/contacts/users/api/formOptions.html.twig', [
            'contacts' => $contacts
        ]);
    }
}
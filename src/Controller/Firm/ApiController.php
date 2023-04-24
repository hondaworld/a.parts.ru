<?php


namespace App\Controller\Firm;


use App\Model\Firm\Entity\Firm\FirmRepository;
use App\ReadModel\Beznal\BeznalFetcher;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/firm-contacts-and-beznals", name="api_firm_contacts_and_beznals")
     * @param FirmRepository $firmRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function userContactsAndBeznals(FirmRepository $firmRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher, Request $request): Response
    {
        $firm = $firmRepository->get($request->query->get('firmID'));

        $contacts = $contactFetcher->allByFirm($firm);
        $arrContacts = [];
        $contactID = null;
        if ($contacts)
            foreach ($contacts as $contact) {
                $arrContacts[] = [
                    'id' => $contact->contactID,
                    'name' => $contact->getAddress()
                ];
                if ($contact->isMain) $contactID = $contact->contactID;
            }

        $beznals = $beznalFetcher->allByFirm($firm);
        $arrBeznals = [];
        $beznalID = null;
        if ($beznals)
            foreach ($beznals as $beznal) {
                $arrBeznals[] = [
                    'id' => $beznal->beznalID,
                    'name' => $beznal->getRequisite()
                ];
                if ($beznal->isMain) $beznalID = $beznal->beznalID;
            }

        $data = ['contactID' => $contactID, 'contacts' => $arrContacts, 'beznalID' => $beznalID, 'beznals' => $arrBeznals];

        return $this->json($data);
    }
}
<?php


namespace App\Controller\User;


use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Beznal\BeznalFetcher;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\User\UserFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $fetcher;

    public function __construct(UserFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * @Route("/api/users", name="api_users")
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function user(Request $request): Response
    {
        $str = $request->query->get('name');

        $data = [];
        $users = [];
        if (mb_strlen($str, 'UTF-8') >= 3) {
            $users = $this->fetcher->findByName($str);
        }

        if ($users) {
            foreach ($users as $user) {
                $data[] = ['id' => $user->userID, 'name' => $user->getName()];
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/api/user-contacts-and-beznals", name="api_user_contacts_and_beznals")
     * @param UserRepository $userRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @param Request $request
     * @return Response
     */
    public function userContactsAndBeznals(UserRepository $userRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher, Request $request): Response
    {
        $user = $userRepository->get($request->query->get('userID'));

        $contacts = $contactFetcher->allByUser($user);
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

        $beznals = $beznalFetcher->allByUser($user);
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
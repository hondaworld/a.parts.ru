<?php


namespace App\ReadModel\Contact;

use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class ContactFetcher
{
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param Manager $manager
     * @return ContactView[]|null
     */
    public function allByManager(Manager $manager): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'c.contactID',
                'c.townID',
                't.name AS town',
                'r.name AS region',
                'co.name AS country',
                'c.zip',
                'c.street',
                'c.house',
                'c.str',
                'c.kv',
                'c.phonemob',
                'c.email',
                'c.isHide',
                'c.isMain',
            )
            ->from('contacts', 'c')
            ->innerJoin('c', 'towns', 't', 'c.townID = t.townID')
            ->innerJoin('t', 'townRegions', 'r', 't.regionID = r.regionID')
            ->innerJoin('t', 'townTypes', 'y', 't.typeID = y.id')
            ->innerJoin('r', 'countries', 'co', 'co.countryID = r.countryID')
            ->where('c.managerID = :managerID')
            ->setParameter('managerID', $manager->getId())
            ->orderBy("c.contactID")
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, ContactView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $contactView = new ContactView();
                foreach ($items as $name => $value) {
                    $contactView->$name = $value;
                }
                $result[] = $contactView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param User $user
     * @return ContactView[]|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByUser(User $user): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'c.contactID',
                'c.townID',
                't.name AS town',
                'r.name AS region',
                'co.name AS country',
                'c.zip',
                'c.street',
                'c.house',
                'c.str',
                'c.kv',
                'c.phonemob',
                'c.email',
                'c.isHide',
                'c.isMain',
            )
            ->from('contacts', 'c')
            ->innerJoin('c', 'towns', 't', 'c.townID = t.townID')
            ->innerJoin('t', 'townRegions', 'r', 't.regionID = r.regionID')
            ->innerJoin('t', 'townTypes', 'y', 't.typeID = y.id')
            ->innerJoin('r', 'countries', 'co', 'co.countryID = r.countryID')
            ->where('c.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy("c.contactID")
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, ContactView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $contactView = new ContactView();
                foreach ($items as $name => $value) {
                    $contactView->$name = $value;
                }
                $result[] = $contactView;
            }
        }

        return $result ?: null;
    }

    public function assocAllByUser(User $user): array
    {
        $contacts = $this->allByUser($user);
        if (!$contacts) return [];

        $result = [];
        foreach ($contacts as $contact) {
            $result[$contact->contactID] = $contact->getAddress();
        }

        return $result;
    }

    /**
     * @param Firm $firm
     * @return ContactView[]|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByFirm(Firm $firm): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'c.contactID',
                'c.townID',
                't.name AS town',
                'r.name AS region',
                'co.name AS country',
                'c.zip',
                'c.street',
                'c.house',
                'c.str',
                'c.kv',
                'c.phonemob',
                'c.email',
                'c.isHide',
                'c.isMain',
            )
            ->from('contacts', 'c')
            ->innerJoin('c', 'towns', 't', 'c.townID = t.townID')
            ->innerJoin('t', 'townRegions', 'r', 't.regionID = r.regionID')
            ->innerJoin('t', 'townTypes', 'y', 't.typeID = y.id')
            ->innerJoin('r', 'countries', 'co', 'co.countryID = r.countryID')
            ->where('c.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->orderBy("c.contactID")
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, ContactView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $contactView = new ContactView();
                foreach ($items as $name => $value) {
                    $contactView->$name = $value;
                }
                $result[] = $contactView;
            }
        }

        return $result ?: null;
    }

    public function assocAllByFirm(Firm $firm): array
    {
        $contacts = $this->allByFirm($firm);
        if (!$contacts) return [];

        $result = [];
        foreach ($contacts as $contact) {
            $result[$contact->contactID] = $contact->getAddress();
        }

        return $result;
    }
}
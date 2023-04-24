<?php


namespace App\ReadModel\Beznal;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class BeznalFetcher
{
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param Manager $manager
     * @return BeznalView[]|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByManager(Manager $manager): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'b.beznalID',
                'b.bankID',
                'CONCAT(ba.bik, " - ", ba.name) AS bank',
                'ba.name AS bank_name',
                'b.rasschet',
                'b.isHide',
                'b.isMain',
            )
            ->from('beznals', 'b')
            ->innerJoin('b', 'banks', 'ba', 'b.bankID = ba.bankID')
            ->where('b.managerID = :managerID')
            ->setParameter('managerID', $manager->getId())
            ->orderBy("b.beznalID")
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, BeznalView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $beznalView = new BeznalView();
                foreach ($items as $name => $value) {
                    $beznalView->$name = $value;
                }
                $result[] = $beznalView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param User $user
     * @return BeznalView[]|null
     */
    public function allByUser(User $user): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'b.beznalID',
                'b.bankID',
                'CONCAT(ba.bik, " - ", ba.name) AS bank',
                'ba.name AS bank_name',
                'b.rasschet',
                'b.isHide',
                'b.isMain',
            )
            ->from('beznals', 'b')
            ->innerJoin('b', 'banks', 'ba', 'b.bankID = ba.bankID')
            ->where('b.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy("b.beznalID")
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, BeznalView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $beznalView = new BeznalView();
                foreach ($items as $name => $value) {
                    $beznalView->$name = $value;
                }
                $result[] = $beznalView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param User $user
     * @return array
     */
    public function assocAllByUser(User $user): array
    {
        $beznals = $this->allByUser($user);
        if (!$beznals) return [];

        $result = [];
        foreach ($beznals as $beznal) {
            $result[$beznal->beznalID] = $beznal->getRequisite();
        }

        return $result;
    }

    /**
     * @param Firm $firm
     * @return BeznalView[]|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByFirm(Firm $firm): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'b.beznalID',
                'b.bankID',
                'CONCAT(ba.bik, " - ", ba.name) AS bank',
                'ba.name AS bank_name',
                'b.rasschet',
                'b.isHide',
                'b.isMain',
            )
            ->from('beznals', 'b')
            ->innerJoin('b', 'banks', 'ba', 'b.bankID = ba.bankID')
            ->where('b.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->orderBy("b.beznalID")
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, BeznalView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $beznalView = new BeznalView();
                foreach ($items as $name => $value) {
                    $beznalView->$name = $value;
                }
                $result[] = $beznalView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param Firm $firm
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function assocAllByFirm(Firm $firm): array
    {
        $beznals = $this->allByFirm($firm);
        if (!$beznals) return [];

        $result = [];
        foreach ($beznals as $beznal) {
            $result[$beznal->beznalID] = $beznal->getRequisite();
        }

        return $result;
    }
}
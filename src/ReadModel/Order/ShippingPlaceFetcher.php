<?php


namespace App\ReadModel\Order;


use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use Doctrine\ORM\EntityManagerInterface;

class ShippingPlaceFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ShippingPlace::class);
    }

    public function get(int $id): ShippingPlace
    {
        return $this->repository->get($id);
    }

//    public function assocByGroup(int $templateGroupID): array
//    {
//        $qb = $this->connection->createQueryBuilder()
//            ->select('templateID, name')
//            ->from('templates')
//            ->where('templateGroupID = :templateGroupID')
//            ->setParameter('templateGroupID', $templateGroupID)
//            ->orderBy('templateID');
//
//        return $qb->executeQuery()->fetchAllKeyValue();
//    }

    public function allNotShipping(ExpenseDocument $expenseDocument): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('sp.*')
            ->from('shipping_places', 'sp')
            ->innerJoin('sp', 'shippings', 's', 'sp.shippingID = s.shippingID')
            ->where('s.expenseDocumentID = :expenseDocumentID')
            ->setParameter('expenseDocumentID', $expenseDocument->getId())
            ->orderBy('sp.number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}
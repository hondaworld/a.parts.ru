<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Auto\Auto;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class MotoFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Auto ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Auto
    {
        return $this->repository->get($id);
    }

    public function allByUser(User $user): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select("m.*, CONCAT(marka.name, ' ', model.name) AS model_name")
            ->from('motos', 'm')
            ->leftJoin('m', 'moto_model', 'model', 'm.moto_modelID = model.moto_modelID')
            ->leftJoin('model', 'auto_marka', 'marka', 'model.auto_markaID = marka.auto_markaID')
            ->innerJoin('m', 'linkUserMoto', 'l', 'm.motoID = l.motoID')
            ->andWhere('l.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy('model_name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}
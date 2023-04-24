<?php

namespace App\Model\Auto\Entity\MotoModel;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MotoModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method MotoModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method MotoModel[]    findAll()
 * @method MotoModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotoModelRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, MotoModel::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return MotoModel
     */
    public function get(int $id): MotoModel
    {
        if (!$motoModel = $this->find($id)) {
            throw new EntityNotFoundException('Модель не найдена');
        }

        return $motoModel;
    }

    public function add(MotoModel $motoModel): void
    {
        $this->em->persist($motoModel);
    }
}

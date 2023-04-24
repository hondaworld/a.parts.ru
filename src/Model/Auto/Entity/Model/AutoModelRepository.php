<?php

namespace App\Model\Auto\Entity\Model;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AutoModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoModel[]    findAll()
 * @method AutoModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoModelRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, AutoModel::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return AutoModel
     */
    public function get(int $id): AutoModel
    {
        if (!$autoModel = $this->find($id)) {
            throw new EntityNotFoundException('Модель не найдена');
        }

        return $autoModel;
    }

    public function add(AutoModel $autoModel): void
    {
        $this->em->persist($autoModel);
    }
}

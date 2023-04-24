<?php

namespace App\Model\User\Entity\FirmContr;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FirmContr|null find($id, $lockMode = null, $lockVersion = null)
 * @method FirmContr|null findOneBy(array $criteria, array $orderBy = null)
 * @method FirmContr[]    findAll()
 * @method FirmContr[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirmContrRepository extends ServiceEntityRepository
{
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, FirmContr::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return FirmContr
     */
    public function get(int $id): FirmContr
    {
        if (!$firmContr = $this->find($id)) {
            throw new EntityNotFoundException('Контрагент не найден');
        }

        return $firmContr;
    }

    public function add(FirmContr $firmContr): void
    {
        $this->em->persist($firmContr);
    }
}

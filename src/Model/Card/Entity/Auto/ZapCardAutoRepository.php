<?php

namespace App\Model\Card\Entity\Auto;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardAuto|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardAuto|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardAuto[]    findAll()
 * @method ZapCardAuto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardAutoRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardAuto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardAuto
     */
    public function get(int $id): ZapCardAuto
    {
        if (!$zapCardAuto = $this->find($id)) {
            throw new EntityNotFoundException('Применимость не найдена');
        }

        return $zapCardAuto;
    }

    public function add(ZapCardAuto $zapCardAuto): void
    {
        $this->em->persist($zapCardAuto);
    }
}

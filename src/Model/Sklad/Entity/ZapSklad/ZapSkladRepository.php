<?php

namespace App\Model\Sklad\Entity\ZapSklad;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapSklad|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapSklad|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapSklad[]    findAll()
 * @method ZapSklad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapSkladRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapSklad::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapSklad
     */
    public function get(int $id): ZapSklad
    {
        if (!$zapSklad = $this->find($id)) {
            throw new EntityNotFoundException('Склад не найден');
        }

        return $zapSklad;
    }

    /**
     * @return ZapSklad|null
     */
    public function getMain(): ?ZapSklad
    {
        return $this->findOneBy(['isMain' => 1]);
    }

    public function add(ZapSklad $zapSklad): void
    {
        $this->em->persist($zapSklad);
    }

    public function updateMain()
    {
        $this->createQueryBuilder('z')
            ->update()
            ->set('z.isMain', '0')
            ->getQuery()
            ->execute();
    }

    /**
     * @return ZapSklad[]
     */
    public function findAllNotHide(): array
    {
        return $this->findBy(['isHide' => false]);
    }
}

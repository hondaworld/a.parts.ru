<?php

namespace App\Model\Card\Entity\Photo;

use App\Model\Card\Entity\Card\ZapCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardPhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardPhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardPhoto[]    findAll()
 * @method ZapCardPhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardPhotoRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardPhoto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardPhoto
     * @throws EntityNotFoundException
     */
    public function get(int $id): ZapCardPhoto
    {
        if (!$zapCardPhoto = $this->find($id)) {
            throw new EntityNotFoundException('Фотография не найдена');
        }

        return $zapCardPhoto;
    }

    public function add(ZapCardPhoto $zapCardPhoto): void
    {
        $this->em->persist($zapCardPhoto);
    }

    public function updateMain(ZapCard $zapCard): void
    {
        $qb = $this->createQueryBuilder('c')
            ->update()
            ->set('c.isMain', 'false');
        $qb->andWhere('c.zapCard = :zapCard');
        $qb->setParameter('zapCard', $zapCard);
        $qb->getQuery()->execute();
    }

    public function findNotMain(ZapCard $zapCard, ZapCardPhoto $exclude): ?ZapCardPhoto
    {
        $qb = $this->createQueryBuilder('z')
            ->where('z.isMain = false')
            ->andWhere('z.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('z.zapPhotoID <> :zapPhoto')
            ->setParameter('zapPhoto', $exclude)
            ->orderBy('z.zapPhotoID')
            ->setMaxResults(1);

        $qb = $qb->getQuery()
            ->getOneOrNullResult();

        return $qb;
    }
}

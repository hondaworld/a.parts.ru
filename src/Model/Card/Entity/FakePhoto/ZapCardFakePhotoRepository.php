<?php

namespace App\Model\Card\Entity\FakePhoto;

use App\Model\Card\Entity\Card\ZapCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardFakePhoto|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardFakePhoto|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardFakePhoto[]    findAll()
 * @method ZapCardFakePhoto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardFakePhotoRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardFakePhoto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardFakePhoto
     * @throws EntityNotFoundException
     */
    public function get(int $id): ZapCardFakePhoto
    {
        if (!$zapCardFakePhoto = $this->find($id)) {
            throw new EntityNotFoundException('Фотография не найдена');
        }

        return $zapCardFakePhoto;
    }

    public function add(ZapCardFakePhoto $zapCardFakePhoto): void
    {
        $this->em->persist($zapCardFakePhoto);
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

    public function findNotMain(ZapCard $zapCard, ZapCardFakePhoto $exclude): ?ZapCardFakePhoto
    {
        $qb = $this->createQueryBuilder('z')
            ->where('z.isMain = false')
            ->andWhere('z.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('z.zapFakePhotoID <> :zapFakePhoto')
            ->setParameter('zapFakePhoto', $exclude)
            ->orderBy('z.zapFakePhotoID')
            ->setMaxResults(1);

        $qb = $qb->getQuery()
            ->getOneOrNullResult();

        return $qb;
    }
}

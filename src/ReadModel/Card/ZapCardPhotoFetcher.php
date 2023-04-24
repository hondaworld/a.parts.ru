<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Photo\ZapCardPhoto;
use Doctrine\ORM\EntityManagerInterface;

class ZapCardPhotoFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardPhoto ::class);
    }

    public function get(int $id): ZapCardPhoto
    {
        return $this->repository->get($id);
    }

    /**
     * @param array $zapCardId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByZapCards(array $zapCardId): array
    {
        if (!$zapCardId) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "zapCardID",
                "bimage"
            )
            ->from('zapPhotos', 'p')
            ->andWhere('p.isMain = 1')
            ->groupBy('zapCardID');
        $qb->andWhere($qb->expr()->in('zapCardID', $zapCardId));

        return $qb->executeQuery()->fetchAllKeyValue();
    }

}
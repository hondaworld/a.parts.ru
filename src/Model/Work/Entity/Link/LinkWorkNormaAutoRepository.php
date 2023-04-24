<?php

namespace App\Model\Work\Entity\Link;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\EntityNotFoundException;
use App\Model\Work\Entity\Group\WorkGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinkWorkNormaAuto|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkWorkNormaAuto|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkWorkNormaAuto[]    findAll()
 * @method LinkWorkNormaAuto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkWorkNormaAutoRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, LinkWorkNormaAuto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return LinkWorkNormaAuto
     * @throws EntityNotFoundException
     */
    public function get(int $id): LinkWorkNormaAuto
    {
        if (!$linkWorkNormaAuto = $this->find($id)) {
            throw new EntityNotFoundException('Ссылка не найдена');
        }

        return $linkWorkNormaAuto;
    }

    public function add(LinkWorkNormaAuto $linkWorkNormaAuto): void
    {
        $this->em->persist($linkWorkNormaAuto);
    }

    public function remove(LinkWorkNormaAuto $linkWorkNormaAuto): void
    {
        $this->em->remove($linkWorkNormaAuto);
    }

    /**
     * @param WorkGroup $workGroup
     * @return LinkWorkNormaAuto[]
     */
    public function findByWorkGroup(WorkGroup $workGroup): array
    {
        return $this->findBy(['workGroup' => $workGroup]);
    }

    public function deleteByWorkGroupAndAutoMarka(WorkGroup $workGroup, AutoMarka $autoMarka): int
    {
        return $this->em->createQueryBuilder()
            ->delete('Work:Link\LinkWorkNormaAuto', 'l')
            ->andWhere('l.workGroup = :workGroup')
            ->setParameter('workGroup', $workGroup)
            ->andWhere('l.auto_marka = :auto_marka')
            ->setParameter('auto_marka', $autoMarka)
            ->getQuery()
            ->execute()
            ;
    }

    public function deleteByWorkGroupAndAutoModel(WorkGroup $workGroup, AutoModel $autoModel): int
    {
        return $this->em->createQueryBuilder()
            ->delete('Work:Link\LinkWorkNormaAuto', 'l')
            ->andWhere('l.workGroup = :workGroup')
            ->setParameter('workGroup', $workGroup)
            ->andWhere('l.auto_model = :auto_model')
            ->setParameter('auto_model', $autoModel)
            ->getQuery()
            ->execute()
            ;
    }

    public function deleteByWorkGroupAndAutoGeneration(WorkGroup $workGroup, AutoGeneration $autoGeneration): int
    {
        return $this->em->createQueryBuilder()
            ->delete('Work:Link\LinkWorkNormaAuto', 'l')
            ->andWhere('l.workGroup = :workGroup')
            ->setParameter('workGroup', $workGroup)
            ->andWhere('l.auto_generation = :auto_generation')
            ->setParameter('auto_generation', $autoGeneration)
            ->getQuery()
            ->execute()
            ;
    }

    public function deleteByWorkGroupAndAutoModification(WorkGroup $workGroup, AutoModification $autoModification): int
    {
        return $this->em->createQueryBuilder()
            ->delete('Work:Link\LinkWorkNormaAuto', 'l')
            ->andWhere('l.workGroup = :workGroup')
            ->setParameter('workGroup', $workGroup)
            ->andWhere('l.auto_modification= :auto_modification')
            ->setParameter('auto_modification', $autoModification)
            ->getQuery()
            ->execute()
            ;
    }
}

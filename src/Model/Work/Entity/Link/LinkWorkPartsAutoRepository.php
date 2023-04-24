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
 * @method LinkWorkPartsAuto|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkWorkPartsAuto|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkWorkPartsAuto[]    findAll()
 * @method LinkWorkPartsAuto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkWorkPartsAutoRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, LinkWorkPartsAuto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return LinkWorkPartsAuto
     * @throws EntityNotFoundException
     */
    public function get(int $id): LinkWorkPartsAuto
    {
        if (!$linkWorkPartsAuto = $this->find($id)) {
            throw new EntityNotFoundException('Ссылка не найдена');
        }

        return $linkWorkPartsAuto;
    }

    public function add(LinkWorkPartsAuto $linkWorkPartsAuto): void
    {
        $this->em->persist($linkWorkPartsAuto);
    }

    public function remove(LinkWorkPartsAuto $linkWorkPartsAuto): void
    {
        $this->em->remove($linkWorkPartsAuto);
    }

    /**
     * @param WorkGroup $workGroup
     * @return LinkWorkPartsAuto[]
     */
    public function findByWorkGroup(WorkGroup $workGroup): array
    {
        return $this->findBy(['workGroup' => $workGroup]);
    }

    public function deleteByWorkGroupAndAutoMarka(WorkGroup $workGroup, AutoMarka $autoMarka): int
    {
        return $this->em->createQueryBuilder()
            ->delete('Work:Link\LinkWorkPartsAuto', 'l')
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
            ->delete('Work:Link\LinkWorkPartsAuto', 'l')
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
            ->delete('Work:Link\LinkWorkPartsAuto', 'l')
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
            ->delete('Work:Link\LinkWorkPartsAuto', 'l')
            ->andWhere('l.workGroup = :workGroup')
            ->setParameter('workGroup', $workGroup)
            ->andWhere('l.auto_modification = :auto_modification')
            ->setParameter('auto_modification', $autoModification)
            ->getQuery()
            ->execute()
            ;
    }
}

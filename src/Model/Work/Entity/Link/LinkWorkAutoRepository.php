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
 * @method LinkWorkAuto|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkWorkAuto|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkWorkAuto[]    findAll()
 * @method LinkWorkAuto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkWorkAutoRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, LinkWorkAuto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return LinkWorkAuto
     * @throws EntityNotFoundException
     */
    public function get(int $id): LinkWorkAuto
    {
        if (!$linkWorkAuto = $this->find($id)) {
            throw new EntityNotFoundException('Ссылка не найдена');
        }

        return $linkWorkAuto;
    }

    public function add(LinkWorkAuto $linkWorkAuto): void
    {
        $this->em->persist($linkWorkAuto);
    }

    public function remove(LinkWorkAuto $linkWorkAuto): void
    {
        $this->em->remove($linkWorkAuto);
    }

    /**
     * @param WorkGroup $workGroup
     * @return LinkWorkAuto[]
     */
    public function findByWorkGroup(WorkGroup $workGroup): array
    {
        return $this->findBy(['workGroup' => $workGroup]);
    }

    public function deleteByWorkGroupAndAutoMarka(WorkGroup $workGroup, AutoMarka $autoMarka): int
    {
        return $this->em->createQueryBuilder()
            ->delete('Work:Link\LinkWorkAuto', 'l')
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
            ->delete('Work:Link\LinkWorkAuto', 'l')
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
            ->delete('Work:Link\LinkWorkAuto', 'l')
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
            ->delete('Work:Link\LinkWorkAuto', 'l')
            ->andWhere('l.workGroup = :workGroup')
            ->setParameter('workGroup', $workGroup)
            ->andWhere('l.auto_modification = :auto_modification')
            ->setParameter('auto_modification', $autoModification)
            ->getQuery()
            ->execute()
            ;
    }
}

<?php

namespace App\Model\User\Entity\TemplateGroup;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TemplateGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method TemplateGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method TemplateGroup[]    findAll()
 * @method TemplateGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateGroupRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, TemplateGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return TemplateGroup
     */
    public function get(int $id): TemplateGroup
    {
        if (!$templateGroup = $this->find($id)) {
            throw new EntityNotFoundException('Группа шаблонов не найдена');
        }
        return $templateGroup;
    }

    public function add(TemplateGroup $templateGroup): void
    {
        $this->em->persist($templateGroup);
    }
}

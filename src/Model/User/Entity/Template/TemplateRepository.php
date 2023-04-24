<?php

namespace App\Model\User\Entity\Template;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Template|null find($id, $lockMode = null, $lockVersion = null)
 * @method Template|null findOneBy(array $criteria, array $orderBy = null)
 * @method Template[]    findAll()
 * @method Template[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Template::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Template
     */
    public function get(int $id): Template
    {
        if (!$template = $this->find($id)) {
            throw new EntityNotFoundException('Шаблон не найден');
        }
        return $template;
    }

    public function add(Template $template): void
    {
        $this->em->persist($template);
    }
}

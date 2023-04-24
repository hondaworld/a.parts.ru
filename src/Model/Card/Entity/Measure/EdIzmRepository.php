<?php

namespace App\Model\Card\Entity\Measure;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EdIzm|null find($id, $lockMode = null, $lockVersion = null)
 * @method EdIzm|null findOneBy(array $criteria, array $orderBy = null)
 * @method EdIzm[]    findAll()
 * @method EdIzm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EdIzmRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, EdIzm::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return EdIzm
     */
    public function get(int $id): EdIzm
    {
        if (!$edIzm = $this->find($id)) {
            throw new EntityNotFoundException('Единица измерения не найдена');
        }

        return $edIzm;
    }

    public function add(EdIzm $edIzm): void
    {
        $this->em->persist($edIzm);
    }
}

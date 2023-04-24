<?php

namespace App\Model\Contact\Entity\Contact;

use App\Model\Contact\Entity\Town\Town;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{

    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Contact::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Contact
     */
    public function get(int $id): Contact
    {
        if (!$contact = $this->find($id)) {
            throw new EntityNotFoundException('Контакт не найден');
        }

        return $contact;
    }

    public function hasByTown(Town $town): bool
    {
        $query = $this->createQueryBuilder('c')
            ->select('COUNT(c.contactID)')
            ->andWhere('c.town = :town')
            ->setParameter('town', $town->getId());

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function add(Contact $contact): void
    {
        $this->em->persist($contact);
    }
}

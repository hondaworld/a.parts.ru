<?php

namespace App\Model\Contact\Entity\Country;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Country|null find($id, $lockMode = null, $lockVersion = null)
 * @method Country|null findOneBy(array $criteria, array $orderBy = null)
 * @method Country[]    findAll()
 * @method Country[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CountryRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Country::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Country
     */
    public function get(int $id): Country
    {
        if (!$country = $this->find($id)) {
            throw new EntityNotFoundException('Страна не найдена');
        }

        return $country;
    }

    /**
     * @param string $name
     * @return Country
     */
    public function getByName(string $name): ?Country
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function add(Country $country): void
    {
        $this->em->persist($country);
    }
}

<?php


namespace App\ReadModel\Manager;


use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu;
use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\ORM\EntityManagerInterface;

class FavouriteMenuFetcher
{
    private $connection;
    private $favouriteMenuRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->favouriteMenuRepository = $em->getRepository(FavouriteMenu::class);
    }

    public function get(int $id): FavouriteMenu
    {
        return $this->favouriteMenuRepository->get($id);
    }

    public function all(int $managerID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'f.id',
                'f.name',
                'f.url',
                'f.sort',
                's.name AS menu_section',
                's.url AS menu_section_url',
                's.icon AS menu_section_icon',
            )
            ->from('favouriteMenu', 'f')
            ->leftJoin('f', 'menu_sections', 's', 's.id = f.menu_section_id')
            ->andWhere('f.manager_id = :managerID')
            ->setParameter('managerID', $managerID)
            ->orderBy('sort')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }
}
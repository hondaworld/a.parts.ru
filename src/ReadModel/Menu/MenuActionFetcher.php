<?php


namespace App\ReadModel\Menu;


use App\Model\EntityNotFoundException;
use App\Model\Menu\Entity\Action\MenuAction;
use Doctrine\ORM\EntityManagerInterface;

class MenuActionFetcher
{
    private $connection;
    private $actions;

    public const STANDART_ACTIONS = [
        ['icon' => 'fas fa-table', 'name' => 'index', 'label' => 'Список'],
        ['icon' => 'fas fa-plus', 'name' => 'create', 'label' => 'Добавить'],
        ['icon' => 'fas fa-edit', 'name' => 'edit', 'label' => 'Изменить'],
        ['icon' => 'far fa-trash-alt', 'name' => 'delete', 'label' => 'Удалить'],
        ['icon' => 'far fa-eye-slash', 'name' => 'hide', 'label' => 'Скрыть'],
        ['icon' => 'far fa-eye', 'name' => 'unhide', 'label' => 'Восстановить']
    ];

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->actions = $em->getRepository(MenuAction::class);
    }

    public function get(int $id): MenuAction
    {

        if (!$action = $this->actions->find($id)) {
            throw new EntityNotFoundException('Операция секции меню не найдена');
        }

        return $action;
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name'
            )
            ->from('menu_actions', 'g')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(int $sectionID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                's.id',
                's.name',
                's.label',
                's.icon',
                's.url',
                's.isHide'
            )
            ->from('menu_actions', 's')
            ->where('s.menu_section_id = :sectionID')
            ->setParameter('sectionID', $sectionID)
            ->orderBy('name')
            ->executeQuery();


        return $qb->fetchAllAssociative();
    }

    public function findWithSections(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.id',
                'a.name',
                'a.label',
                'a.icon',
                'a.url',
                'a.isHide',
                'a.menu_section_id',
                's.name AS menu_section',
                's.menu_group_id',
                'g.name AS menu_group'
            )
            ->from('menu_actions', 'a')
            ->innerJoin('a', 'menu_sections', 's', 'a.menu_section_id = s.id')
            ->innerJoin('s', 'menu_groups', 'g', 's.menu_group_id = g.id')
            ->orderBy('g.sort')
            ->addOrderBy('s.name')
            ->addOrderBy('a.label')
            ->executeQuery();
        $actions = $qb->fetchAllAssociative();

        $arr = [];

        foreach ($actions as $action) {
            $arr[$action['menu_group_id']]['sections'][$action['menu_section_id']]['actions'][] = $action;
            $arr[$action['menu_group_id']]['name'] = $action['menu_group'];
            $arr[$action['menu_group_id']]['sections'][$action['menu_section_id']]['name'] = $action['menu_section'];
        }

        $sort = [
            'index' => 1,
            'show' => 2,
            'create' => 3,
            'edit' => 4,
            'delete' => 5,
            'hide' => 6,
            'unhide' => 7,
        ];

        foreach ($arr as $menu_group_id => &$group) {
            foreach ($group['sections'] as $menu_section_id => &$section) {
                uasort($section['actions'], function($a, $b) use ($sort) {
                    $sort1 = $sort[$a['name']] ?? 1000;
                    $sort2 = $sort[$b['name']] ?? 1000;
                    if ($sort1 != $sort2) return $sort1 - $sort2;
                    return $a['label'] <=> $b['label'];
                });
            }
        }

        return $arr;
    }

}
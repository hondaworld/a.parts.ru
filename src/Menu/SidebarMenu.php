<?php

namespace App\Menu;

use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\ReadModel\Manager\FavouriteMenuFetcher;
use App\ReadModel\Menu\MenuGroupFetcher;
use App\ReadModel\Menu\MenuSectionFetcher;
use App\Security\Voter\MenuVoter;
use App\Service\MultiMenu;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\RegexVoter;
use Knp\Menu\Matcher\Voter\RouteVoter;
use Knp\Menu\Matcher\Voter\UriVoter;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class SidebarMenu implements ContainerAwareInterface
{
    private $factory;
    private $auth;
    private $groups;
    private $sections;
    private $multiMenu;
    private $matcher;
    private $manager;
    private $request;
    private $favouriteMenu;
    private ManagerRepository $managerRepository;

    use ContainerAwareTrait;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth, MenuGroupFetcher $groupFetcher, MenuSectionFetcher $sectionFetcher, MultiMenu $multiMenu, Security $security, FavouriteMenuFetcher $favouriteMenuFetcher)
    {
        $this->factory = $factory;
        $this->auth = $auth;
        $this->groups = $groupFetcher->menu();
        $this->sections = $sectionFetcher->menu();
        $this->multiMenu = $multiMenu;
        $this->manager = $security->getUser();
        $this->request = Request::createFromGlobals();

        $arUrl = explode('/', trim($this->request->getPathInfo(), '/'));
        $url = '/';
        $voters = [];
        foreach ($arUrl as $item) {
            $url .= $item . '/';
            $voters[] = new RegexVoter('|^' . $url . '|');
//            $voters[] = new UriVoter($url);
        }

        $this->matcher = new Matcher([new MenuVoter()]);
//        dump($voters);
        $this->favouriteMenu = $favouriteMenuFetcher->all($security->getUser()->getId());
    }

    public function build(array $options): ItemInterface
    {
//        $options['currentClass'] = 'active';

        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav-main']);

        if ($this->favouriteMenu) {

            $menu->addChild('favourite', ['uri' => '#'])
                ->setLabel('Избранное меню')
                ->setAttribute('class', 'nav-main-item')
                ->setLinkAttributes(['class' => 'nav-main-link nav-main-link-submenu', 'data-toggle' => 'submenu', 'aria-haspopup' => 'true', 'aria-expanded' => 'false'])
                ->setExtra('icon', 'far fa-star')
                ->setChildrenAttribute('class', 'nav-main-submenu');

            foreach ($this->favouriteMenu as $favouriteMenu) {
                $menu['favourite']->addChild('fav_' . $favouriteMenu['id'], ['uri' => $favouriteMenu['menu_section_url'] ?: $favouriteMenu['url']])
                    ->setLabel($favouriteMenu['name'])
                    ->setAttribute('class', 'nav-main-item')
                    ->setLinkAttribute('class', 'nav-main-link')
                    ->setExtra('icon', $favouriteMenu['menu_section_icon'] ?: 'fas fa-angle-double-right');
            }
        }

        foreach ($this->groups as $group) {

            if (isset($this->sections[$group['id']])) {
                $menu->addChild('group' . $group['id'])
                    ->setLabel($group['name'])
                    ->setAttribute('class', 'nav-main-heading')
                    ->setExtra('icon', $group['icon'])
                    ->setChildrenAttribute('class', 'nav-main-submenu');

                $countPrev = count($menu->getChildren());
                $menu = $this->generateIndents($menu, $this->multiMenu->getMenu($this->sections[$group['id']]));
                $count = count($menu->getChildren());

                if ($countPrev == $count) {
                    unset($menu['group' . $group['id']]);
                }
            }
        }


        return $menu;
    }

    /**
     * Рекурсивный метод для формирования отступов. Чем больше потомков, тем больше отступ.
     *
     * @param ItemInterface $menu
     * @param array $data
     * @return ItemInterface
     */
    private function generateIndents(ItemInterface $menu, array $data): ItemInterface
    {
        foreach ($data as $node) {

//            dump($node['name']);
            if ((!$node['entity'] || $node['entity'] && $this->manager->getActionByEntity($node['entity'], 'index')) && !$node['isHide']) {

                if ($node['url'] || isset($node['childs'])) {
                    $menu->addChild($node['id'], ['uri' => (isset($node['childs']) || !$node['url']) ? '#' : $node['url']])
                        ->setLabel($node['name'])
                        ->setAttribute('class', 'nav-main-item');
                    if ($node['icon']) $menu[$node['id']]->setExtra('icon', $node['icon']);

                    $matcher = $this->matcher;
                    if ($node['pattern']) {
                        $menu[$node['id']]->setExtra('pattern', $node['pattern']);
                    }

                    if ($matcher->isCurrent($menu[$node['id']])) $menu[$node['id']]->setCurrent(true);

                    if (isset($node['childs'])) {
                        $menu[$node['id']]->setLinkAttributes(['class' => 'nav-main-link nav-main-link-submenu', 'data-toggle' => 'submenu', 'aria-haspopup' => 'true', 'aria-expanded' => 'false']);
                        $menu[$node['id']]->setChildrenAttribute('class', 'nav-main-submenu');

                        $child = $this->factory->createItem('child' . $node['id']);
                        $child = ($this->generateIndents($child, $node['childs']));
                        $menu[$node['id']]->setChildren($child->getChildren());

                        if ($matcher->isAncestor($menu[$node['id']])) {
                            $menu[$node['id']]->setAttribute('class', 'nav-main-item open');
                        }

                        if (count($menu[$node['id']]->getChildren()) == 0) {
                            //$menu->removeChild($node['name']);
                            unset($menu[$node['id']]);
                        }
                    } else {
                        $menu[$node['id']]->setLinkAttribute('class', 'nav-main-link');
                    }
                }
            }
        }

        return $menu;
    }
}

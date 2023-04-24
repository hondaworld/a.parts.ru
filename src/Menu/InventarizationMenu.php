<?php


namespace App\Menu;


use App\Model\Card\Entity\Inventarization\InventarizationRepository;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class InventarizationMenu implements ContainerAwareInterface
{
    private $factory;
    private $auth;
    private $matcher;
    private $manager;
    private $request;
    private $inventarizations;


    use ContainerAwareTrait;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth, InventarizationRepository $inventarizations, Security $security)
    {
        $this->factory = $factory;
        $this->auth = $auth;
        $this->manager = $security->getUser();
        $this->request = Request::createFromGlobals();
        $this->inventarizations = $inventarizations;
    }

    public function build(array $options): ItemInterface
    {
        $inventarization = $this->inventarizations->get($options['inventarizationID']);

        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav-main nav-main-horizontal nav-main-hover']);

        if ($this->auth->isGranted('index', 'Inventarization')) {

            $menu->addChild('Инвентаризация', [
                'route' => 'inventarizations.goods.inventarization',
                'routeParameters' => ['id' => $options['inventarizationID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-boxes')
                ->setLinkAttribute('class', 'nav-main-link');

            $menu->addChild('Товары', [
                'route' => 'inventarizations.goods',
                'routeParameters' => ['id' => $options['inventarizationID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-cogs')
                ->setLinkAttribute('class', 'nav-main-link');

            $menu->addChild('Сканирование', [
                'route' => 'inventarizations.goods.scan.search',
                'routeParameters' => ['id' => $options['inventarizationID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-barcode')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        return $menu;
    }
}
<?php


namespace App\Menu;


use App\Model\Manager\Entity\Manager\ManagerRepository;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class ManagerMenu implements ContainerAwareInterface
{
    private $factory;
    private $auth;
    private $matcher;
    private $manager;
    private $request;
    private $managers;


    use ContainerAwareTrait;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth, ManagerRepository $managers, Security $security)
    {
        $this->factory = $factory;
        $this->auth = $auth;
        $this->manager = $security->getUser();
        $this->request = Request::createFromGlobals();
        $this->managers = $managers;
    }

    public function build(array $options): ItemInterface
    {
        $manager = $this->managers->get($options['managerID']);

        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav-main nav-main-horizontal nav-main-hover']);

        if ($this->auth->isGranted('edit', 'Manager')) {

            $menu->addChild('Сотрудник', [
                'route' => 'managers.edit',
                'routeParameters' => ['id' => $options['managerID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-user-tie')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('contacts', $manager)) {

            $menu->addChild('Контакты', [
                'route' => 'managers.contacts',
                'routeParameters' => ['managerID' => $options['managerID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-map-marker-alt')
                ->setExtra('badge', count($manager->getContacts()))
                ->setExtra('pattern', '^/managers/contacts/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('beznals', $manager)) {

            $menu->addChild('Реквизиты', [
                'route' => 'managers.beznals',
                'routeParameters' => ['managerID' => $options['managerID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-file-invoice-dollar')
                ->setExtra('badge', count($manager->getBeznals()))
                ->setExtra('pattern', '^/managers/beznals/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('documents', $manager)) {

            $menu->addChild('Документы', [
                'route' => 'managers.documents',
                'routeParameters' => ['managerID' => $options['managerID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-folder')
                ->setExtra('badge', count($manager->getDocuments()))
                ->setExtra('pattern', '^/managers/documents/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('firms', $manager)) {

            $menu->addChild('Организации', [
                'route' => 'managers.firms',
                'routeParameters' => ['managerID' => $options['managerID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-user')
                ->setExtra('badge', count($manager->getManagerFirms()))
                ->setExtra('pattern', '^/managers/firms/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('auth', $manager)) {

            $menu->addChild('Авторизации', [
                'route' => 'managers.auth',
                'routeParameters' => ['managerID' => $options['managerID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-sign-in-alt')
                ->setExtra('pattern', '^/managers/auth/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        return $menu;
    }
}
<?php


namespace App\Menu;


use App\Model\User\Entity\User\UserRepository;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class UserMenu implements ContainerAwareInterface
{
    private $factory;
    private $auth;
    private $matcher;
    private $manager;
    private $request;
    private $users;


    use ContainerAwareTrait;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth, UserRepository $users, Security $security)
    {
        $this->factory = $factory;
        $this->auth = $auth;
        $this->manager = $security->getUser();
        $this->request = Request::createFromGlobals();
        $this->users = $users;
    }

    public function build(array $options): ItemInterface
    {
        $user = $this->users->get($options['userID']);

        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav-main nav-main-horizontal nav-main-hover']);

        if ($this->auth->isGranted('show', 'User')) {

            $menu->addChild('Клиент', [
                'route' => 'users.show',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-user')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('dop', $user)) {

            $menu->addChild('Дополнительно', [
                'route' => 'users.dop',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-users-cog')
                ->setExtra('pattern', '^/users/dop/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('contacts', $user)) {

            $menu->addChild('Контакты', [
                'route' => 'users.contacts',
                'routeParameters' => ['userID' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-map-marker-alt')
                ->setExtra('badge', count($user->getContacts()))
                ->setExtra('pattern', '^/users/contacts/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('beznals', $user)) {

            $menu->addChild('Реквизиты', [
                'route' => 'users.beznals',
                'routeParameters' => ['userID' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-file-invoice-dollar')
                ->setExtra('badge', count($user->getBeznals()))
                ->setExtra('pattern', '^/users/beznals/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('documents', $user)) {

            $menu->addChild('Документы', [
                'route' => 'users.documents',
                'routeParameters' => ['userID' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-folder')
                ->setExtra('badge', count($user->getDocuments()))
                ->setExtra('pattern', '^/users/documents/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('index', 'Auto')) {

            $menu->addChild('Автомобили', [
                'route' => 'users.auto',
                'routeParameters' => ['userID' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-car')
                ->setExtra('badge', count($user->getAutos()))
                ->setExtra('pattern', '^/users/auto/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('index', 'Auto')) {

            $menu->addChild('Мотоциклы', [
                'route' => 'users.moto',
                'routeParameters' => ['userID' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-motorcycle')
                ->setExtra('badge', count($user->getMotos()))
                ->setExtra('pattern', '^/users/moto/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('settings', $user)) {

            $menu->addChild('Настройки', [
                'route' => 'users.settings',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-cogs')
                ->setExtra('pattern', '^/users/settings/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('balance', $user)) {

            $menu->addChild('Баланс', [
                'route' => 'users.balance.history',
                'routeParameters' => ['userID' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-receipt')
                ->setExtra('pattern', '^/users/balance/history/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('show', 'Order')) {

            $menu->addChild('Товары', [
                'route' => 'order.goods',
                'routeParameters' => ['id' => $options['userID'], 'reset' => 1]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-cogs')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        return $menu;
    }
}
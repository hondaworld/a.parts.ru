<?php


namespace App\Menu;


use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\User\Entity\User\UserRepository;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class ZapCardMenu implements ContainerAwareInterface
{
    private $factory;
    private $auth;
    private $matcher;
    private $manager;
    private $request;
    private $cards;


    use ContainerAwareTrait;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth, ZapCardRepository $cards, Security $security)
    {
        $this->factory = $factory;
        $this->auth = $auth;
        $this->manager = $security->getUser();
        $this->request = Request::createFromGlobals();
        $this->cards = $cards;
    }

    public function build(array $options): ItemInterface
    {
        $user = $this->cards->get($options['zapCardID']);

        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav-main nav-main-horizontal nav-main-hover']);

        if ($this->auth->isGranted('show', 'ZapCard')) {

            $menu->addChild('Информация', [
                'route' => 'card.parts.show',
                'routeParameters' => ['id' => $options['zapCardID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-cog')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('show', 'ZapCard')) {

            $menu->addChild('Цены', [
                'route' => 'card.parts.prices',
                'routeParameters' => ['id' => $options['zapCardID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-ruble-sign')
                ->setExtra('pattern', '^/card/parts/prices/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('show', 'ZapCard')) {

            $menu->addChild('Фото', [
                'route' => 'card.parts.photos',
                'routeParameters' => ['id' => $options['zapCardID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-images')
                ->setExtra('pattern', '^/card/parts/photos/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('show', 'ZapCard')) {

            $menu->addChild('Фото подделок', [
                'route' => 'card.parts.fakePhotos',
                'routeParameters' => ['id' => $options['zapCardID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-images')
                ->setExtra('pattern', '^/card/parts/fakePhotos/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('index', 'ShopZamena')) {

            $menu->addChild('Замены', [
                'route' => 'card.parts.zamena',
                'routeParameters' => ['id' => $options['zapCardID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-cogs')
                ->setExtra('pattern', '^/card/parts/zamena/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('show', 'ZapCard')) {

            $menu->addChild('Применимость', [
                'route' => 'card.parts.auto',
                'routeParameters' => ['id' => $options['zapCardID']],
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('pattern', '^/card/parts/auto/')
                ->setExtra('icon', 'fas fa-car')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('show', 'ZapCard')) {

            $menu->addChild('Склады', [
                'route' => 'card.parts.sklad',
                'routeParameters' => ['id' => $options['zapCardID']],
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('pattern', '^/card/parts/sklad/')
                ->setExtra('icon', 'fas fa-warehouse')
                ->setLinkAttribute('class', 'nav-main-link');
        }

//        if ($this->auth->isGranted('dop', $user)) {
//
//            $menu->addChild('Дополнительно', [
//                'route' => 'users.dop',
//                'routeParameters' => ['id' => $options['userID']],
//                'pattern' => '/^users/dop/'
//            ])
//                ->setAttribute('class', 'nav-main-item')
//                ->setExtra('icon', 'fas fa-users-cog')
//                ->setLinkAttribute('class', 'nav-main-link');
//        }
//
//        if ($this->auth->isGranted('contacts', $user)) {
//
//            $menu->addChild('Контакты', [
//                'route' => 'users.contacts',
//                'routeParameters' => ['userID' => $options['userID']],
//                'pattern' => '/^users/contacts/'
//            ])
//                ->setAttribute('class', 'nav-main-item')
//                ->setExtra('icon', 'fas fa-map-marker-alt')
//                ->setExtra('badge', count($user->getContacts()))
//                ->setLinkAttribute('class', 'nav-main-link');
//        }
//
//        if ($this->auth->isGranted('beznals', $user)) {
//
//            $menu->addChild('Реквизиты', [
//                'route' => 'users.beznals',
//                'routeParameters' => ['userID' => $options['userID']],
//                'pattern' => '/^users/beznals/'
//            ])
//                ->setAttribute('class', 'nav-main-item')
//                ->setExtra('icon', 'fas fa-file-invoice-dollar')
//                ->setExtra('badge', count($user->getBeznals()))
//                ->setLinkAttribute('class', 'nav-main-link');
//        }
//
//        if ($this->auth->isGranted('documents', $user)) {
//
//            $menu->addChild('Документы', [
//                'route' => 'users.documents',
//                'routeParameters' => ['userID' => $options['userID']],
//                'pattern' => '/^users/documents/'
//            ])
//                ->setAttribute('class', 'nav-main-item')
//                ->setExtra('icon', 'far fa-folder')
//                ->setExtra('badge', count($user->getDocuments()))
//                ->setLinkAttribute('class', 'nav-main-link');
//        }
//
//        if ($this->auth->isGranted('settings', $user)) {
//
//            $menu->addChild('Настройки', [
//                'route' => 'users.settings',
//                'routeParameters' => ['id' => $options['userID']],
//                'pattern' => '/^users/settings/'
//            ])
//                ->setAttribute('class', 'nav-main-item')
//                ->setExtra('icon', 'fas fa-cogs')
//                ->setLinkAttribute('class', 'nav-main-link');
//        }
//
//        if ($this->auth->isGranted('balance', $user)) {
//
//            $menu->addChild('Баланс', [
//                'route' => 'users.balance.history',
//                'routeParameters' => ['userID' => $options['userID']],
//                'pattern' => '/^users/balance/history/'
//            ])
//                ->setAttribute('class', 'nav-main-item')
//                ->setExtra('icon', 'fas fa-receipt')
//                ->setLinkAttribute('class', 'nav-main-link');
//        }

        return $menu;
    }
}
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

class OrderMenu implements ContainerAwareInterface
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

        if ($this->auth->isGranted('edit', 'Order')) {

            $menu->addChild('Документ', [
                'route' => 'orders.show',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-file-alt')
                ->setLinkAttribute('class', 'nav-main-link');

        }

        if ($this->auth->isGranted('show', 'Order')) {

            $menu->addChild('Товары', [
                'route' => 'order.goods',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-cogs')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('order_paid', 'Order')) {

            $menu->addChild('Платежи', [
                'route' => 'order.paids',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-credit-card')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('order_pick', 'Order')) {

            $menu->addChild('Сборка', [
                'route' => 'order.pick.scan',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-clipboard-list')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('order_expense_document', 'Order')) {

            $menu->addChild('Оформление', [
                'route' => 'order.expenseDocument',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-file-contract')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('order_check', 'Order')) {

            $menu->addChild('Чеки', [
                'route' => 'order.checks',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-cash-register')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('user_comments', 'Order')) {

            $menu->addChild('Комментарии', [
                'route' => 'order.user.comments',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-comment')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('user_sms_history', 'Order')) {

            $menu->addChild('SMS', [
                'route' => 'order.sms.history',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-sms')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('order_shipping', 'Order')) {

            $menu->addChild('Отгрузки', [
                'route' => 'order.shippings',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-truck')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('manager_order_operations', 'Order')) {

            $menu->addChild('Операции', [
                'route' => 'order.manager.operations',
                'routeParameters' => ['id' => $options['userID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-user-edit')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        return $menu;
    }
}
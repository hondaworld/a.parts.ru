<?php


namespace App\Menu;


use App\Model\Firm\Entity\Firm\FirmRepository;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class FirmMenu implements ContainerAwareInterface
{
    private $factory;
    private $auth;
    private $matcher;
    private $firm;
    private $request;
    private $firms;


    use ContainerAwareTrait;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth, FirmRepository $firms, Security $security)
    {
        $this->factory = $factory;
        $this->auth = $auth;
        $this->firm = $security->getUser();
        $this->request = Request::createFromGlobals();
        $this->firms = $firms;
    }

    public function build(array $options): ItemInterface
    {
        $firm = $this->firms->get($options['firmID']);

        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav-main nav-main-horizontal nav-main-hover']);

        if ($this->auth->isGranted('edit', 'Firm')) {

            $menu->addChild('Организация', [
                'route' => 'firms.edit',
                'routeParameters' => ['id' => $options['firmID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-building')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('contacts', $firm)) {

            $menu->addChild('Контакты', [
                'route' => 'firms.contacts',
                'routeParameters' => ['firmID' => $options['firmID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-map-marker-alt')
                ->setExtra('badge', count($firm->getContacts()))
                ->setExtra('pattern', '^/firms/contacts/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('beznals', $firm)) {

            $menu->addChild('Реквизиты', [
                'route' => 'firms.beznals',
                'routeParameters' => ['firmID' => $options['firmID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-file-invoice-dollar')
                ->setExtra('badge', count($firm->getBeznals()))
                ->setExtra('pattern', '^/firms/beznals/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('documents', $firm)) {

            $menu->addChild('Документы', [
                'route' => 'firms.documents',
                'routeParameters' => ['firmID' => $options['firmID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-folder')
                ->setExtra('badge', count($firm->getDocuments()))
                ->setExtra('pattern', '^/firms/documents/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('managers', $firm)) {

            $menu->addChild('Сотрудники', [
                'route' => 'firms.managers',
                'routeParameters' => ['firmID' => $options['firmID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'far fa-user')
                ->setExtra('badge', count($firm->getManagerFirms()))
                ->setExtra('pattern', '^/firms/managers/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('others', $firm)) {

            $menu->addChild('Дополнительно', [
                'route' => 'firms.others',
                'routeParameters' => ['id' => $options['firmID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-file-invoice')
                ->setExtra('pattern', '^/firms/others/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        if ($this->auth->isGranted('firm_balance', 'Firm')) {

            $menu->addChild('Взаиморасчеты', [
                'route' => 'firms.balance.history',
                'routeParameters' => ['firmID' => $options['firmID']]
            ])
                ->setAttribute('class', 'nav-main-item')
                ->setExtra('icon', 'fas fa-receipt')
                ->setExtra('pattern', '^/firms/balance/history/')
                ->setLinkAttribute('class', 'nav-main-link');
        }

        return $menu;
    }
}
<?php


namespace App\Controller\Manager;


use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Manager\Filter;
use App\ReadModel\Manager\ManagerAuthFetcher;
use App\Security\Voter\Manager\ManagerVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/managers/auth", name="managers.auth")
 */
class ManagerAuthController extends AbstractController
{
    /**
     * @Route("/{managerID}/", name="")
     * @param Manager $manager
     * @param Request $request
     * @param ManagerAuthFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Manager $manager, Request $request, ManagerAuthFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_AUTH, $manager);

        $settings = $settings->get('managerAuth');

        $filter = new Filter\Auth\Filter();
        $filter->inPage = isset($settings['inPage']) ? $settings['inPage'] : $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Auth\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $manager,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/managers/auth/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'manager' => $manager,
        ]);
    }
}
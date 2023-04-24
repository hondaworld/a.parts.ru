<?php

namespace App\Controller\Order;

use App\Model\EntityNotFoundException;
use App\Model\Order\Entity\Site\Site;
use App\Model\Order\Entity\Site\SiteRepository;
use App\Model\Order\UseCase\Site\Edit;
use App\Model\Order\UseCase\Site\Create;
use App\Model\Flusher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/sites", name="sites")
 */
class SitesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param SiteRepository $siteRepository
     * @return Response
     */
    public function index(SiteRepository $siteRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Site');

        $all = $siteRepository->findBy([], ['name' => 'asc']);

        return $this->render('app/orders/sites/index.html.twig', [
            'all' => $all
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Site $site
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Site $site, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Site');

        $command = Edit\Command::fromEntity($site);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('sites');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/sites/edit.html.twig', [
            'form' => $form->createView(),
            'site' => $site
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Site $site
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Site $site, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Site');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($site->getOrders()) > 0) {
                $data = ['code' => 500, 'message' => 'У сайта есть заказы'];
            } else {
                $em->remove($site);
                $flusher->flush();
                $data['message'] = 'Сайт удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Site');

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('sites');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/sites/create.html.twig', [
            'form' => $form->createView()
        ]);

    }
}

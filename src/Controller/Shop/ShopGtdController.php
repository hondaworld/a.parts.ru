<?php

namespace App\Controller\Shop;

use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use App\Model\Shop\Entity\Gtd\ShopGtdRepository;
use App\ReadModel\Shop\Filter;
use App\Model\Shop\UseCase\ShopGtd\Create;
use App\Model\Shop\UseCase\ShopGtd\Edit;
use App\ReadModel\Shop\ShopGtdFetcher;
use \App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/gtd", name="shop.gtd")
 */
class ShopGtdController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ShopGtdFetcher $fetcher
     * @param Request $request
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(ShopGtdFetcher $fetcher, Request $request, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopGtd');

        $settings = $settings->get('shopGtd');

        $filter = new Filter\Gtd\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Gtd\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/shop/shopGtd/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopGtd');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.gtd', ['page' => $request->getSession()->get('page/shopGtd') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/shopGtd/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ShopGtd $shopGtd
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ShopGtd $shopGtd, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ShopGtd');

        $command = Edit\Command::fromDocument($shopGtd);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.gtd', ['page' => $request->getSession()->get('page/shopGtd') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/shopGtd/edit.html.twig', [
            'form' => $form->createView(),
            'shopGtd' => $shopGtd,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ShopGtdRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, ShopGtdRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopGtd');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $shopGtd = $repository->get($id);

            if (count($shopGtd->getIncomes()) > 0 || count($shopGtd->getIncomes1())) {
                return $this->json(['code' => 500, 'message' => 'ГТД используется']);
            }

            $em->remove($shopGtd);
            $flusher->flush();
            $data['message'] = 'ГТД удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

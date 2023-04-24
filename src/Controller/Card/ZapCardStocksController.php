<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Card\Entity\Measure\EdIzmRepository;
use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Card\Entity\Stock\ZapCardStockRepository;
use App\Model\EntityNotFoundException;
use App\Model\Card\UseCase\Stock\Edit;
use App\Model\Card\UseCase\Stock\Create;
use App\Model\Flusher;
use App\ReadModel\Card\ZapCardStockFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/stocks", name="card.stocks")
 */
class ZapCardStocksController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ZapCardStockFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(ZapCardStockFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCardStock');

        $settings = $settings->get('zapCardStocks');

        $pagination = $fetcher->all($settings);

        return $this->render('app/card/stocks/index.html.twig', [
            'pagination' => $pagination,
            'table_checkable' => true,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ZapCardStock');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.stocks');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/stocks/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ZapCardStock $zapCardStock
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ZapCardStock $zapCardStock, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCardStock');

        $command = Edit\Command::fromEntity($zapCardStock);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.stocks');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/stocks/edit.html.twig', [
            'form' => $form->createView(),
            'stock' => $zapCardStock
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ZapCardStockRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, ZapCardStockRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ZapCardStock');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $zapCardStock = $repository->get($id);
            $em->remove($zapCardStock);
            $flusher->flush();
            $data['message'] = 'Акция удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ZapCardStockRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ZapCardStockRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'ZapCardStock');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapCardStock = $repository->get($request->query->getInt('id'));
            $zapCardStock->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/unHide", name=".unHide")
     * @param Request $request
     * @param ZapCardStockRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ZapCardStockRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'ZapCardStock');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapCardStock = $repository->get($request->query->getInt('id'));
            $zapCardStock->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

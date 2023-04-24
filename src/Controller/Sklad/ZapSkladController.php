<?php

namespace App\Controller\Sklad;

use App\Model\EntityNotFoundException;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\UseCase\ZapSklad\Edit;
use App\Model\Sklad\UseCase\ZapSklad\Create;
use App\Model\Flusher;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/sklads", name="sklads")
 */
class ZapSkladController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ZapSkladFetcher $fetcher
     * @return Response
     */
    public function index(ZapSkladFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapSklad');

        $zapSklads = $fetcher->all();

        return $this->render('app/sklads/index.html.twig', [
            'zapSklads' => $zapSklads,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ZapSklad');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('sklads');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/sklads/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ZapSklad $zapSklad, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapSklad');

        $command = Edit\Command::fromEntity($zapSklad);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('sklads');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/sklads/edit.html.twig', [
            'form' => $form->createView(),
            'zapSklad' => $zapSklad
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ZapSkladRepository $zapSkladRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, ZapSkladRepository $zapSkladRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        return $this->json(['code' => 403, 'message' => 'Лучше не стоит удалять склад']);

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $zapSklad = $zapSkladRepository->get($id);
            if ($zapSklad->isMain()) {
                try {
                    $zapSkladOsn = $zapSkladRepository->get(ZapSklad::OSN_SKLAD_ID);
                    $zapSkladOsn->setMain();
                } catch (EntityNotFoundException $e) {

                }
            }
            $em->remove($zapSklad);
            $flusher->flush();
            $data['message'] = 'Склад удален';
            $data['reload'] = true;
            $this->addFlash('success', $data['message']);
            return $this->json($data);

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

//        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ZapSkladRepository $zapSkladRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ZapSkladRepository $zapSkladRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapSklad = $zapSkladRepository->get($request->query->getInt('id'));
            if ($zapSklad->isMain()) {
                return $this->json(['code' => 500, 'message' => 'Невозможно скрыть основной склад']);
            }
            $zapSklad->hide();
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
     * @param ZapSkladRepository $zapSkladRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ZapSkladRepository $zapSkladRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapSklad = $zapSkladRepository->get($request->query->getInt('id'));
            $zapSklad->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

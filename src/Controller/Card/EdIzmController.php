<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Category\ZapCategoryRepository;
use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Card\Entity\Measure\EdIzmRepository;
use App\Model\EntityNotFoundException;
use App\Model\Card\UseCase\Measure\Edit;
use App\Model\Card\UseCase\Measure\Create;
use App\Model\Flusher;
use App\ReadModel\Card\EdIzmFetcher;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/measures", name="card.measures")
 */
class EdIzmController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param EdIzmFetcher $fetcher
     * @return Response
     */
    public function index(EdIzmFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'EdIzm');

        $measures = $fetcher->all();

        return $this->render('app/card/measures/index.html.twig', [
            'measures' => $measures,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'EdIzm');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.measures');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/measures/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param EdIzm $edIzm
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(EdIzm $edIzm, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'EdIzm');

        $command = Edit\Command::fromEntity($edIzm);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.measures');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/measures/edit.html.twig', [
            'form' => $form->createView(),
            'edIzm' => $edIzm
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param EdIzmRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, EdIzmRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'EdIzm');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $edIzm = $repository->get($id);

            if (count($edIzm->getZapCards()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить единицу измерения, использующуюся в деталях']);
            }

            $em->remove($edIzm);
            $flusher->flush();
            $data['message'] = 'Единица измерения удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param EdIzmRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, EdIzmRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'EdIzm');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $edIzm = $repository->get($request->query->getInt('id'));
            $edIzm->hide();
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
     * @param EdIzmRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, EdIzmRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'EdIzm');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $edIzm = $repository->get($request->query->getInt('id'));
            $edIzm->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Marka\AutoMarkaRepository;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\Marka\Edit;
use App\Model\Auto\UseCase\Marka\Create;
use App\Model\Flusher;
use App\ReadModel\Auto\AutoMarkaFetcher;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/auto/marka", name="auto.marka")
 */
class AutoMarkaController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param AutoMarkaFetcher $fetcher
     * @return Response
     */
    public function index(AutoMarkaFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $brands = $fetcher->all();

        return $this->render('app/auto/marka/index.html.twig', [
            'brands' => $brands,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.marka');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/marka/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param AutoMarka $autoMarka
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(AutoMarka $autoMarka, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($autoMarka);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.marka');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/marka/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param AutoMarkaRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, AutoMarkaRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $autoMarka = $repository->get($id);

            if (count($autoMarka->getModels()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить марку, имеющую модели автомобилей']);
            }

            if (count($autoMarka->getMotoModels()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить марку, имеющую модели мотоциклов']);
            }

            $em->remove($autoMarka);
            $flusher->flush();
            $data['message'] = 'Марка удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param AutoMarkaRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, AutoMarkaRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoMarka = $repository->get($request->query->getInt('id'));
            $autoMarka->hide();
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
     * @param AutoMarkaRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, AutoMarkaRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoMarka = $repository->get($request->query->getInt('id'));
            $autoMarka->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

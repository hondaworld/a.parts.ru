<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\WorkPeriod\Edit;
use App\Model\Auto\UseCase\WorkPeriod\Create;
use App\Model\Auto\UseCase\WorkPeriod\Copy;
use App\Model\Flusher;
use App\Model\Work\Entity\Period\WorkPeriod;
use App\Model\Work\Entity\Period\WorkPeriodRepository;
use App\ReadModel\Auto\WorkPeriodFetcher;
use App\ReadModel\Work\WorkGroupFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ModelSorter;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/auto/work/period", name="auto.work.period")
 */
class WorkPeriodController extends AbstractController
{
    /**
     * @Route("/{auto_modificationID}/", name="")
     * @param AutoModification $autoModification
     * @param WorkPeriodFetcher $fetcher
     * @return Response
     */
    public function index(AutoModification $autoModification, WorkPeriodFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $periods = $fetcher->allByModification($autoModification);

        return $this->render('app/auto/workPeriod/index.html.twig', [
            'periods' => $periods,
            'autoModification' => $autoModification,
            'table_sortable' => true,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{auto_modificationID}/create", name=".create")
     * @param AutoModification $autoModification
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(AutoModification $autoModification, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $autoModification);
                return $this->redirectToRoute('auto.work.period', ['auto_modificationID' => $autoModification->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/workPeriod/create.html.twig', [
            'form' => $form->createView(),
            'autoModification' => $autoModification
        ]);
    }

    /**
     * @Route("/{auto_modificationID}/copy", name=".copy")
     * @param AutoModification $autoModification
     * @param Request $request
     * @param Copy\Handler $handler
     * @return Response
     */
    public function copy(AutoModification $autoModification, Request $request, Copy\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = new Copy\Command($autoModification);

        $form = $this->createForm(Copy\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $autoModification);
                return $this->redirectToRoute('auto.work.period', ['auto_modificationID' => $autoModification->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/workPeriod/copy.html.twig', [
            'form' => $form->createView(),
            'autoModification' => $autoModification
        ]);
    }

    /**
     * @Route("/{auto_modificationID}/{id}/edit", name=".edit")
     * @ParamConverter("autoModification", options={"id" = "auto_modificationID"})
     * @param AutoModification $autoModification
     * @param WorkPeriod $workPeriod
     * @param Request $request
     * @param Edit\Handler $handler
     * @param WorkGroupFetcher $workGroupFetcher
     * @return Response
     */
    public function edit(AutoModification $autoModification, WorkPeriod $workPeriod, Request $request, Edit\Handler $handler, WorkGroupFetcher $workGroupFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($workPeriod);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.work.period', ['auto_modificationID' => $autoModification->getId(), 'id' => $workPeriod->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/workPeriod/edit.html.twig', [
            'form' => $form->createView(),
            'workPeriod' => $workPeriod,
            'autoModification' => $autoModification
        ]);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param WorkPeriodRepository $workPeriodRepository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     */
    public function sort(int $id, WorkPeriodRepository $workPeriodRepository, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $workPeriod = $workPeriodRepository->get($id);

            $oldSort = $workPeriod->getNumber();
            $newSort = $sorter->getNewSort($workPeriod->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $workPeriodRepository->removeSort($workPeriod->getAutoModification(), $oldSort);
                $workPeriodRepository->addSort($workPeriod->getAutoModification(), $newSort);

                $workPeriod->changeNumber($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_modificationID}/{id}/delete", name=".delete")
     * @ParamConverter("autoModification", options={"id" = "auto_modificationID"})
     * @param AutoModification $autoModification
     * @param WorkPeriod $workPeriod
     * @param WorkPeriodRepository $workPeriodRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(AutoModification $autoModification, WorkPeriod $workPeriod, WorkPeriodRepository $workPeriodRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
//            if (count($autoModification->getModifications()) > 0 || count($autoModification->getEngines()) > 0) {
//                return $this->json(['code' => 500, 'message' => 'Невозможно удалить поколение, содержащую модификации']);
//            }

            $workPeriodRepository->removeSort($autoModification, $workPeriod->getNumber());
            $em->remove($workPeriod);
            $flusher->flush();
            $data['message'] = 'ТО удалено';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_modificationID}/hide", name=".hide")
     * @ParamConverter("autoModification", options={"id" = "auto_modificationID"})
     * @param Request $request
     * @param WorkPeriodRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, WorkPeriodRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $workPeriod = $repository->get($request->query->getInt('id'));
            $workPeriod->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_modificationID}/unHide", name=".unHide")
     * @ParamConverter("autoModification", options={"id" = "auto_modificationID"})
     * @param Request $request
     * @param WorkPeriodRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, WorkPeriodRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $workPeriod = $repository->get($request->query->getInt('id'));
            $workPeriod->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

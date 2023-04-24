<?php

namespace App\Controller\Firm;

use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\OrgJob\OrgJob;
use App\Model\Firm\Entity\OrgJob\OrgJobRepository;
use App\Model\Flusher;
use App\Model\Firm\UseCase\OrgJob\Create;
use App\Model\Firm\UseCase\OrgJob\Edit;
use App\ReadModel\Firm\OrgJobFetcher;
use \App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/org/jobs", name="org.jobs")
 */
class OrgJobsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param OrgJobFetcher $fetcher
     * @return Response
     */
    public function index(OrgJobFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgJob');

        $orgJobs = $fetcher->all();

        return $this->render('app/firms/orgJobs/index.html.twig', [
            'orgJobs' => $orgJobs,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgJob');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('org.jobs');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/orgJobs/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param OrgJob $orgJob
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(OrgJob $orgJob, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgJob');

        $command = Edit\Command::fromEntity($orgJob);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('org.jobs');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/orgJobs/edit.html.twig', [
            'form' => $form->createView(),
            'orgJob' => $orgJob,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param OrgJob $orgJob
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(OrgJob $orgJob, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgJob');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($orgJob->getManagerFirms()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить должность, прикрепленную к менеджеру']);
            } else {
                $em->remove($orgJob);
                $flusher->flush();
                $data['message'] = 'Должность удалена';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param OrgJobRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, OrgJobRepository $repository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgJob');

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $orgJob = $repository->get($request->query->getInt('id'));
            $orgJob->hide();
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
     * @param OrgJobRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, OrgJobRepository $repository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgJob');

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $orgJob = $repository->get($request->query->getInt('id'));
            $orgJob->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

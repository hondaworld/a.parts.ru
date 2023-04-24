<?php

namespace App\Controller\Firm;

use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\OrgGroup\OrgGroup;
use App\Model\Firm\Entity\OrgGroup\OrgGroupRepository;
use App\Model\Flusher;
use App\Model\Firm\UseCase\OrgGroup\Create;
use App\Model\Firm\UseCase\OrgGroup\Edit;
use App\ReadModel\Firm\OrgGroupFetcher;
use \App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/org/groups", name="org.groups")
 */
class OrgGroupsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param OrgGroupFetcher $fetcher
     * @return Response
     */
    public function index(OrgGroupFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgGroup');

        $orgGroups = $fetcher->all();

        return $this->render('app/firms/orgGroups/index.html.twig', [
            'orgGroups' => $orgGroups,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgGroup');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('org.groups');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/orgGroups/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param OrgGroup $orgGroup
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(OrgGroup $orgGroup, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgGroup');

        $command = Edit\Command::fromEntity($orgGroup);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('org.groups');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/orgGroups/edit.html.twig', [
            'form' => $form->createView(),
            'orgGroup' => $orgGroup,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param OrgGroup $orgGroup
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(OrgGroup $orgGroup, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgGroup');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($orgGroup->getManagerFirms()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить подразделение, прикрепленное к организациям']);
            } else {
                $em->remove($orgGroup);
                $flusher->flush();
                $data['message'] = 'Подразделение удалено';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param OrgGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, OrgGroupRepository $repository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgGroup');

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $orgGroup = $repository->get($request->query->getInt('id'));
            $orgGroup->hide();
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
     * @param OrgGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, OrgGroupRepository $repository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'OrgGroup');

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $orgGroup = $repository->get($request->query->getInt('id'));
            $orgGroup->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

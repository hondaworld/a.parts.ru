<?php

namespace App\Controller\Manager;

use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\Model\Manager\UseCase\Group\Create;
use App\Model\Manager\UseCase\Group\Edit;
use App\ReadModel\Manager\ManagerGroupFetcher;
use App\ReadModel\Menu\MenuActionFetcher;
use \App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/managers/groups", name="managers.groups")
 */
class ManagerGroupsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ManagerGroupFetcher $fetcher
     * @return Response
     */
    public function index(ManagerGroupFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ManagerGroup');

        $groups = $fetcher->all();

        return $this->render('app/managers/groups/index.html.twig', [
            'groups' => $groups,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ManagerGroup');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.groups');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/groups/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ManagerGroup $group
     * @param Request $request
     * @param MenuActionFetcher $actionFetcher
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ManagerGroup $group, Request $request, MenuActionFetcher $actionFetcher, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ManagerGroup');

        $actionsList = $actionFetcher->findWithSections();

        $command = Edit\Command::fromManagerGroup($group, $actionsList);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.groups');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/groups/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
            'actionsList' => $actionsList
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param ManagerGroupRepository $groups
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, Request $request, ManagerGroupRepository $groups, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ManagerGroup');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $manager = $groups->get($id);
            $em->remove($manager);
            $flusher->flush();
            $data['message'] = 'Группа менеджеров удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

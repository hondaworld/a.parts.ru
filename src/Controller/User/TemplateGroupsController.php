<?php

namespace App\Controller\User;

use App\Model\EntityNotFoundException;
use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use App\Model\User\Entity\TemplateGroup\TemplateGroupRepository;
use App\Model\User\UseCase\TemplateGroup\Edit;
use App\Model\User\UseCase\TemplateGroup\Create;
use App\Model\Flusher;
use App\ReadModel\User\TemplateGroupFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Security\Voter\User\TemplateVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/template/groups", name="template.groups")
 */
class TemplateGroupsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param TemplateGroupFetcher $fetcher
     * @return Response
     */
    public function index(TemplateGroupFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Template');

        $templateGroups = $fetcher->all();

        return $this->render('app/users/templateGroups/index.html.twig', [
            'templateGroups' => $templateGroups
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
        $this->denyAccessUnlessGranted(TemplateVoter::TEMPLATE_GROUP_CREATE, 'Template');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('template.groups');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/templateGroups/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param TemplateGroup $templateGroup
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(TemplateGroup $templateGroup, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TemplateVoter::TEMPLATE_GROUP_EDIT, 'Template');

        $command = Edit\Command::fromEntity($templateGroup);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('template.groups');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/templateGroups/edit.html.twig', [
            'form' => $form->createView(),
            'templateGroup' => $templateGroup
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param TemplateGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, TemplateGroupRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(TemplateVoter::TEMPLATE_GROUP_DELETE, 'Template');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $templateGroup = $repository->get($id);

            if (count($templateGroup->getTemplates()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить группу, содержащую шаблоны']);
            }

            if ($templateGroup->isNoneDelete()) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить группу']);
            }

            $em->remove($templateGroup);
            $flusher->flush();
            $data['message'] = 'Группа шаблонов удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

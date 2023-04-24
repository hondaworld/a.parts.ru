<?php

namespace App\Controller\User;

use App\Model\EntityNotFoundException;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use App\Model\User\UseCase\Template\Edit;
use App\Model\User\UseCase\Template\Create;
use App\Model\Flusher;
use App\ReadModel\User\TemplateFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/templates", name="templates")
 */
class TemplatesController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param TemplateGroup $templateGroup
     * @param TemplateFetcher $fetcher
     * @return Response
     */
    public function index(TemplateGroup $templateGroup, TemplateFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Template');

        $templates = $fetcher->allByGroup($templateGroup);

        return $this->render('app/users/templates/index.html.twig', [
            'templates' => $templates,
            'templateGroup' => $templateGroup
        ]);
    }

    /**
     * @Route("/create/{id}", name=".create")
     * @param TemplateGroup $templateGroup
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(TemplateGroup $templateGroup, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Template');

        $command = new Create\Command($templateGroup);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('templates', ['id' => $templateGroup->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/templates/create.html.twig', [
            'form' => $form->createView(),
            'templateGroup' => $templateGroup
        ]);
    }

    /**
     * @Route("/{templateGroupID}/{id}/edit", name=".edit")
     * @ParamConverter("templateGroup", options={"id" = "templateGroupID"})
     * @param TemplateGroup $templateGroup
     * @param Template $template
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(TemplateGroup $templateGroup, Template $template, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Template');

        $command = Edit\Command::fromEntity($template);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('templates', ['id' => $templateGroup->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/templates/edit.html.twig', [
            'form' => $form->createView(),
            'templateGroup' => $templateGroup,
            'template' => $template
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param TemplateRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, TemplateRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Template');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $template = $repository->get($id);
            $em->remove($template);
            $flusher->flush();
            $data['message'] = 'Шаблон удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

<?php

namespace App\Controller\Work;

use App\Model\EntityNotFoundException;
use App\Model\Work\UseCase\Group\Edit;
use App\Model\Work\UseCase\Group\Create;
use App\Model\Flusher;
use App\Model\Work\Entity\Category\WorkCategory;
use App\Model\Work\Entity\Group\WorkGroup;
use App\ReadModel\Work\WorkGroupFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Security\Voter\Work\WorkCategoryVoter;
use App\Service\ManagerSettings;
use App\ReadModel\Work\Filter;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/work/groups", name="work.groups")
 */
class WorkGroupController extends AbstractController
{
    /**
     * @Route("/{workCategoryID}/", name="")
     * @param WorkCategory $workCategory
     * @param Request $request
     * @param WorkGroupFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(WorkCategory $workCategory, Request $request, WorkGroupFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'WorkCategory');

        $settings = $settings->get('workGroup');

        $filter = new Filter\WorkGroup\Filter();
//        $filter->inPage = isset($settings['inPage']) ? $settings['inPage'] : $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\WorkGroup\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->allByCategory(
            $filter,
            $workCategory,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/work/groups/index.html.twig', [
            'to_array' => WorkGroup::TO,
            'pagination' => $pagination,
            'workCategory' => $workCategory,
            'filter' => $form->createView()
        ]);
    }

    /**
     * @Route("/{workCategoryID}/create", name=".create")
     * @param WorkCategory $workCategory
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(WorkCategory $workCategory, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(WorkCategoryVoter::WORK_GROUP_CHANGE, $workCategory);

        $command = new Create\Command($workCategory);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.groups', ['workCategoryID' => $workCategory->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/groups/create.html.twig', [
            'form' => $form->createView(),
            'workCategory' => $workCategory
        ]);
    }

    /**
     * @Route("/{workCategoryID}/{id}/edit", name=".edit")
     * @ParamConverter("workCategory", options={"id" = "workCategoryID"})
     * @param WorkCategory $workCategory
     * @param WorkGroup $workGroup
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(WorkCategory $workCategory, WorkGroup $workGroup, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(WorkCategoryVoter::WORK_GROUP_CHANGE, $workCategory);

        $command = Edit\Command::fromEntity($workGroup);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.groups', ['workCategoryID' => $workCategory->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/groups/edit.html.twig', [
            'form' => $form->createView(),
            'workCategory' => $workCategory,
            'workGroup' => $workGroup
        ]);
    }

    /**
     * @Route("/{workCategoryID}/{id}/delete", name=".delete")
     * @ParamConverter("workCategory", options={"id" = "workCategoryID"})
     * @param WorkCategory $workCategory
     * @param WorkGroup $workGroup
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(WorkCategory $workCategory, WorkGroup $workGroup, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(WorkCategoryVoter::WORK_GROUP_DELETE, $workCategory);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
//            if (count($workGroup->getZapCards()) > 0) {
//                return $this->json(['code' => 500, 'message' => 'Невозможно удалить группу, содержащую детали']);
//            }

            $em->remove($workGroup);
            $flusher->flush();
            $data['message'] = 'Группа работ удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

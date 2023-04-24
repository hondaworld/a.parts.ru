<?php


namespace App\Controller\Menu;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Menu\Entity\Action\MenuAction;
use App\Model\Menu\Entity\Action\MenuActionRepository;
use App\Model\Menu\Entity\Section\MenuSection;
use App\Model\Menu\UseCase\Action\CreateAll;
use App\Model\Menu\UseCase\Action\Create;
use App\Model\Menu\UseCase\Action\Edit;
use App\ReadModel\Menu\MenuActionFetcher;
use App\ReadModel\Menu\MenuSectionFetcher;
use App\Service\MultiMenu;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/menu/actions", name="menu.actions")
 */
class MenuActionsController extends AbstractController
{
    /**
     * @Route("/{sectionID}/", name="")
     * @ParamConverter("section", options={"id" = "sectionID"})
     * @param MenuSection $section
     * @param Request $request
     * @param MenuActionFetcher $fetcher
     * @param MenuSectionFetcher $fetcherSection
     * @param MultiMenu $multiMenu
     * @return Response
     */
    public function index(MenuSection $section, Request $request, MenuActionFetcher $fetcher, MenuSectionFetcher $fetcherSection, MultiMenu $multiMenu): Response
    {
        $actions = $fetcher->all($section->getId());

        $allParents = $fetcherSection->allSortedWithKeyId();

        $arBreadCrumb = $multiMenu->getBreadCrumb($allParents, $section->getParentId(), 'name', $section->getName() . ' - операции');

        return $this->render('app/menu/actions/index.html.twig', [
            'actions' => $actions,
            'section' => $section,
            'arBreadCrumb' => $arBreadCrumb,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{sectionID}/create", name=".create")
     * @ParamConverter("section", options={"id" = "sectionID"})
     * @param MenuSection $section
     * @param Request $request
     * @param MenuActionFetcher $fetcher
     * @param MenuSectionFetcher $fetcherSection
     * @param MultiMenu $multiMenu
     * @param Create\Handler $handler
     * @param CreateAll\Handler $handlerAll
     * @return Response
     */
    public function create(MenuSection $section, Request $request, MenuActionFetcher $fetcher, MenuSectionFetcher $fetcherSection, MultiMenu $multiMenu, Create\Handler $handler, CreateAll\Handler $handlerAll): Response
    {
        $allParents = $fetcherSection->allSortedWithKeyId();

        $arBreadCrumb = $multiMenu->getBreadCrumb($allParents, $section->getParentId(), 'name', 'операции');

        $commandAll = new CreateAll\Command($section);

        $formAll = $this->createForm(CreateAll\Form::class, $commandAll);
        $formAll->handleRequest($request);

        $command = new Create\Command($section);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('menu.actions', ['sectionID' => $section->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        if ($formAll->isSubmitted() && $formAll->isValid()) {
            try {
                $handlerAll->handle($commandAll);
                return $this->redirectToRoute('menu.actions', ['sectionID' => $section->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/menu/actions/create.html.twig', [
            'form' => $form->createView(),
            'formAll' => $formAll->createView(),
            'section' => $section,
            'newActions' => $commandAll->newActions,
            'arBreadCrumb' => $arBreadCrumb,
        ]);
    }

    /**
     * @Route("/{sectionID}/{id}/edit", name=".edit")
     * @ParamConverter("section", options={"id" = "sectionID"})
     * @param MenuSection $section
     * @param MenuAction $action
     * @param Request $request
     * @param MenuActionFetcher $fetcher
     * @param MenuSectionFetcher $fetcherSection
     * @param MultiMenu $multiMenu
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(MenuSection $section, MenuAction $action, Request $request, MenuActionFetcher $fetcher, MenuSectionFetcher $fetcherSection, MultiMenu $multiMenu, Edit\Handler $handler): Response
    {
        $allParents = $fetcherSection->allSortedWithKeyId();

        $arBreadCrumb = $multiMenu->getBreadCrumb($allParents, $section->getParentId(), 'name', 'операции');

        $command = Edit\Command::fromMenuAction($action);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('menu.actions', ['sectionID' => $section->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/menu/actions/edit.html.twig', [
            'form' => $form->createView(),
            'action' => $action,
            'section' => $section,
            'arBreadCrumb' => $arBreadCrumb,
        ]);
    }

    /**
     * @Route("/{sectionID}/{id}/delete", name=".delete")
     * @ParamConverter("section", options={"id" = "sectionID"})
     * @param MenuSection $section
     * @param int $id
     * @param Request $request
     * @param MenuActionRepository $actions
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(MenuSection $section, int $id, Request $request, MenuActionRepository $actions, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $action = $actions->get($id);
            $em->remove($action);
            $flusher->flush();
            $data['message'] = 'Операция удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{sectionID}/deleteSelected", name=".deleteSelected")
     * @ParamConverter("section", options={"id" = "sectionID"})
     * @param MenuSection $section
     * @param Request $request
     * @param MenuActionRepository $actions
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function deleteSelected(MenuSection $section, Request $request, MenuActionRepository $actions, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $action = $actions->get($request->query->getInt('id'));
            $em->remove($action);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
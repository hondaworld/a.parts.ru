<?php


namespace App\Controller\Menu;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Menu\Entity\Group\MenuGroup;
use App\Model\Menu\Entity\Section\MenuSection;
use App\Model\Menu\Entity\Section\MenuSectionRepository;
use App\Model\Menu\UseCase\Section\Create;
use App\Model\Menu\UseCase\Section\Edit;
use App\ReadModel\DropDownList;
use App\ReadModel\Menu\MenuGroupFetcher;
use App\ReadModel\Menu\MenuSectionFetcher;
use App\Service\ModelSorter;
use App\Service\MultiMenu;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/menu/sections", name="menu.sections")
 */
class MenuSectionsController extends AbstractController
{
    /**
     * @Route("/{groupID}/{parentID}/", name="")
     * @ParamConverter("group", options={"id" = "groupID"})
     * @param MenuGroup $group
     * @param int $parentID
     * @param Request $request
     * @param MenuSectionFetcher $fetcher
     * @param MultiMenu $multiMenu
     * @return Response
     */
    public function index(MenuGroup $group, int $parentID, Request $request, MenuSectionFetcher $fetcher, MultiMenu $multiMenu): Response
    {
        $sections = $fetcher->all($group->getId(), $parentID, $request->query->get('sort'), $request->query->get('direction'));

        $allParents = $fetcher->allSortedWithKeyId();

        $title = $group->getName() . ($parentID != 0 ? $multiMenu::RAZD . $multiMenu->getTitle($allParents, $parentID, 'name') : '');

        $arBreadCrumb = $multiMenu->getBreadCrumb($allParents, $parentID, 'name');

        return $this->render('app/menu/sections/index.html.twig', [
            'sections' => $sections,
            'group' => $group,
            'parentID' => $parentID,
            'title' => $title,
            'arBreadCrumb' => $arBreadCrumb,
            'table_sortable' => true,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{groupID}/{parentID}/create", name=".create")
     * @ParamConverter("group", options={"id" = "groupID"})
     * @param MenuGroup $group
     * @param int $parentID
     * @param Request $request
     * @param MenuSectionFetcher $fetcher
     * @param MultiMenu $multiMenu
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(MenuGroup $group, int $parentID, Request $request, MenuSectionFetcher $fetcher, MultiMenu $multiMenu, Create\Handler $handler): Response
    {
        $allParents = $fetcher->allSortedWithKeyId();

        $arBreadCrumb = $multiMenu->getBreadCrumb($allParents, $parentID, 'name', 'Добавление');

        $command = new Create\Command($group, $parentID);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('menu.sections', ['groupID' => $group->getId(), 'parentID' => $parentID]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/menu/sections/create.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
            'parentID' => $parentID,
            'arBreadCrumb' => $arBreadCrumb,
        ]);
    }

    /**
     * @Route("/{groupID}/{parentID}/{id}/edit", name=".edit")
     * @ParamConverter("group", options={"id" = "groupID"})
     * @param MenuGroup $group
     * @param int $parentID
     * @param MenuSection $section
     * @param Request $request
     * @param MenuSectionFetcher $fetcher
     * @param MenuGroupFetcher $fetcherGroup
     * @param MultiMenu $multiMenu
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(MenuGroup $group, int $parentID, MenuSection $section, Request $request, MenuSectionFetcher $fetcher, MenuGroupFetcher $fetcherGroup, MultiMenu $multiMenu, Edit\Handler $handler): Response
    {
        $allParents = $fetcher->allSortedWithKeyId();

        $arBreadCrumb = $multiMenu->getBreadCrumb($allParents, $parentID, 'name', $section->getName());

        $command = Edit\Command::fromMenuSection($section);


        $groups = $fetcherGroup->assoc();
        $allParents = $fetcher->allSortedWithKeyId();
        $arDropDownList = $multiMenu->getDropDownList($allParents, 'name', $command->id);

        $dropDownList = [];
        foreach ($groups as $groupID => $groupName) {
            $dropDownList[] = new DropDownList($groupID . ',0', $groupName, ['menu_group_id' => $groupID, 'id' => 0]);
            foreach ($arDropDownList as $dropDown) {
                if ($dropDown->item['menu_group_id'] == $groupID) {
                    $dropDown->id = $groupID . ',' . $dropDown->id;
                    $dropDown->name = MultiMenu::RAZD . $dropDown->name;
                    $dropDownList[] = $dropDown;
                }
            }
        }
        $command->dropDownList = $dropDownList;

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('menu.sections', ['groupID' => $group->getId(), 'parentID' => $parentID]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/menu/sections/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
            'parentID' => $parentID,
            'section' => $section,
            'arBreadCrumb' => $arBreadCrumb,
        ]);
    }

    /**
     * @Route("/{groupID}/{parentID}/{id}/delete", name=".delete")
     * @ParamConverter("group", options={"id" = "groupID"})
     * @param MenuGroup $group
     * @param int $parentID
     * @param int $id
     * @param Request $request
     * @param MenuSectionRepository $sections
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(MenuGroup $group, int $parentID, int $id, Request $request, MenuSectionRepository $sections, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $section = $sections->get($id);
            $children = $sections->findByParentId($id);
            if ($children) {
                foreach ($children as $modelSection) {
                    $modelSection->changeParentId($parentID);
                    $modelSection->changeSort($section->getSort() + $modelSection->getSort() - 1);
                }
                $sections->changeSort($group, $parentID, $section->getSort(), count($children) - 1);
            } else {
                $sections->removeSort($group, $parentID, $section->getSort());
            }
            $em->remove($section);
            $flusher->flush();
            $this->addFlash('succes', 'Секция меню удалена');
            $data['reload'] = true;

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param Request $request
     * @param MenuSectionRepository $sections
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function sort(int $id, Request $request, MenuSectionRepository $sections, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $section = $sections->get($id);

            $oldSort = $section->getSort();
            $newSort = $sorter->getNewSort($section->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $sections->removeSort($section->getGroup(), $section->getParentId(), $oldSort);
                $sections->addSort($section->getGroup(), $section->getParentId(), $newSort);

                $section->changeSort($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param MenuSectionRepository $sections
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, MenuSectionRepository $sections, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $section = $sections->get($request->query->getInt('id'));
            $section->hide();
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
     * @param MenuSectionRepository $sections
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, MenuSectionRepository $sections, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $section = $sections->get($request->query->getInt('id'));
            $section->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
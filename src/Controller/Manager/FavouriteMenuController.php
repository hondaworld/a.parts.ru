<?php

namespace App\Controller\Manager;

use App\Model\Card\Entity\Category\ZapCategoryRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu;
use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenuRepository;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Manager\UseCase\FavouriteMenu\Create;
use App\Model\Manager\UseCase\FavouriteMenu\Edit;
use App\ReadModel\DropDownList;
use App\ReadModel\Manager\FavouriteMenuFetcher;
use App\ReadModel\Manager\ManagerGroupFetcher;
use App\ReadModel\Menu\MenuActionFetcher;
use App\ReadModel\Menu\MenuGroupFetcher;
use App\ReadModel\Menu\MenuSectionFetcher;
use \App\Security\Voter\StandartActionsVoter;
use App\Service\ModelSorter;
use App\Service\MultiMenu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/favouriteMenu", name="favouriteMenu")
 */
class FavouriteMenuController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param FavouriteMenuFetcher $fetcher
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function index(FavouriteMenuFetcher $fetcher): Response
    {
        $all = $fetcher->all($this->getUser()->getId());

        return $this->render('app/managers/favouriteMenu/index.html.twig', [
            'all' => $all,
            'table_sortable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param MenuSectionFetcher $menuSectionFetcher
     * @param MenuGroupFetcher $menuGroupFetcher
     * @param MultiMenu $multiMenu
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function create(Request $request, MenuSectionFetcher $menuSectionFetcher, MenuGroupFetcher $menuGroupFetcher, MultiMenu $multiMenu, Create\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $manager = $managerRepository->get($this->getUser()->getId());
        $command = new Create\Command();

        $groups = $menuGroupFetcher->assoc();
        $allParents = $menuSectionFetcher->allSortedWithKeyId();
        $arDropDownList = $multiMenu->getDropDownList($allParents, 'name');

        $dropDownList = [];
        foreach ($groups as $groupID => $groupName) {
            $dropDownList[] = new DropDownList($groupID . ',0', $groupName, ['menu_group_id' => $groupID, 'id' => 0, 'url' => '']);
            foreach ($arDropDownList as $dropDown) {
                if ($dropDown->item['menu_group_id'] == $groupID && $dropDown->item['url'] != '' && $dropDown->item['entity'] != '' && $this->isGranted('index', $dropDown->item['entity'])) {
//                    $dropDown->id = $groupID . ',' . $dropDown->id;
                    $dropDown->name = MultiMenu::RAZD . $dropDown->name;
                    $dropDownList[] = $dropDown;
                }
            }
        }
        $command->dropDownList = $dropDownList;

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $manager);
                return $this->redirectToRoute('favouriteMenu');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/favouriteMenu/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param FavouriteMenu $favouriteMenu
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(FavouriteMenu $favouriteMenu, Request $request, Edit\Handler $handler): Response
    {

        $command = Edit\Command::fromEntity($favouriteMenu);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('favouriteMenu');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/favouriteMenu/edit.html.twig', [
            'form' => $form->createView(),
            'favouriteMenu' => $favouriteMenu
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param FavouriteMenuRepository $favouriteMenuRepository
     * @param Flusher $flusher
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function delete(int $id, Request $request, FavouriteMenuRepository $favouriteMenuRepository, Flusher $flusher, ManagerRepository $managerRepository): Response
    {
        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        $em = $this->getDoctrine()->getManager();
        try {
            $favouriteMenu = $favouriteMenuRepository->get($id);
            $favouriteMenuRepository->removeSort($manager, $favouriteMenu->getSort());
            $em->remove($favouriteMenu);
            $flusher->flush();
            $data['message'] = 'Пункт меню удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param FavouriteMenuRepository $repository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function sort(int $id, FavouriteMenuRepository $repository, Flusher $flusher, ModelSorter $sorter, ManagerRepository $managerRepository): Response
    {
        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $favouriteMenu = $repository->get($id);

            $oldSort = $favouriteMenu->getSort();
            $newSort = $sorter->getNewSort($favouriteMenu->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $repository->removeSort($manager, $oldSort);
                $repository->addSort($manager, $newSort);

                $favouriteMenu->changeSort($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

<?php


namespace App\Controller\Menu;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Menu\Entity\Group\MenuGroup;
use App\Model\Menu\Entity\Group\MenuGroupRepository;
use App\Model\Menu\UseCase\Group\Create;
use App\Model\Menu\UseCase\Group\Edit;
use App\ReadModel\Menu\MenuGroupFetcher;
use App\Service\ModelSorter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/menu/groups", name="menu.groups")
 */
class MenuGroupsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param MenuGroupFetcher $fetcher
     * @return Response
     */
    public function index(Request $request, MenuGroupFetcher $fetcher): Response
    {

        $groups = $fetcher->all($request->query->get('sort'), $request->query->get('direction'));

        return $this->render('app/menu/groups/index.html.twig', [
            'groups' => $groups,
            'table_sortable' => true,
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
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('menu.groups');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/menu/groups/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param MenuGroup $group
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(MenuGroup $group, Request $request, Edit\Handler $handler): Response
    {
        $command = Edit\Command::fromMenuGroup($group);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('menu.groups');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/menu/groups/edit.html.twig', [
            'form' => $form->createView(),
            'group' => $group
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param MenuGroupRepository $groups
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, Request $request, MenuGroupRepository $groups, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $group = $groups->get($id);

            if (count($group->getSections()) > 0) {
                return $this->json(['code' => 404, 'message' => "Невозможно удалить группу, содержащую секции"]);
            }

            $groups->removeSort($group->getSort());
            $em->remove($group);
            $flusher->flush();
            $data['message'] = 'Группа меню удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param Request $request
     * @param MenuGroupRepository $groups
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function sort(int $id, Request $request, MenuGroupRepository $groups, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $group = $groups->get($id);

            $oldSort = $group->getSort();
            $newSort = $sorter->getNewSort($group->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $groups->removeSort($oldSort);
                $groups->addSort($newSort);

                $group->changeSort($newSort);
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
     * @param MenuGroupRepository $groups
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, MenuGroupRepository $groups, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $group = $groups->get($request->query->getInt('id'));
            $group->hide();
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
     * @param MenuGroupRepository $groups
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, MenuGroupRepository $groups, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $group = $groups->get($request->query->getInt('id'));
            $group->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
<?php


namespace App\Controller\Work;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Menu\Entity\Group\MenuGroup;
use App\Model\Work\Entity\Category\WorkCategory;
use App\Model\Work\UseCase\Category\Create;
use App\Model\Work\UseCase\Category\Edit;
use App\Model\Work\Entity\Category\WorkCategoryRepository;
use App\ReadModel\Work\WorkCategoryFetcher;
use App\Service\ModelSorter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/categories", name="work.categories")
 */
class WorkCategoriesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param WorkCategoryFetcher $fetcher
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(Request $request, WorkCategoryFetcher $fetcher): Response
    {
        $categories = $fetcher->all();

        return $this->render('app/work/categories/index.html.twig', [
            'categories' => $categories,
            'table_sortable' => true,
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
                return $this->redirectToRoute('work.categories');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/categories/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param WorkCategory $workCategory
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(WorkCategory $workCategory, Request $request, Edit\Handler $handler): Response
    {
        $command = Edit\Command::fromEntity($workCategory);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.categories');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/categories/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $workCategory
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param WorkCategoryRepository $workCategoryRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, WorkCategoryRepository $workCategoryRepository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $category = $workCategoryRepository->get($id);

            if (count($category->getGroups()) > 0) {
                return $this->json(['code' => 404, 'message' => "Невозможно удалить категорию, содержащую группы"]);
            }

            $workCategoryRepository->removeSort($category->getNumber());
            $em->remove($category);
            $flusher->flush();
            $data['message'] = 'Категория работ удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param WorkCategoryRepository $workCategoryRepository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     */
    public function sort(int $id, WorkCategoryRepository $workCategoryRepository, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $category = $workCategoryRepository->get($id);

            $oldSort = $category->getNumber();
            $newSort = $sorter->getNewSort($category->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $workCategoryRepository->removeSort($oldSort);
                $workCategoryRepository->addSort($newSort);

                $category->changeNumber($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
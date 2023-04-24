<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Category\ZapCategoryRepository;
use App\Model\EntityNotFoundException;
use App\Model\Card\UseCase\Category\Edit;
use App\Model\Card\UseCase\Category\Create;
use App\Model\Flusher;
use App\ReadModel\Card\ZapCategoryFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ModelSorter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/categories", name="card.categories")
 */
class ZapCategoryController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ZapCategoryFetcher $fetcher
     * @return Response
     */
    public function index(ZapCategoryFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCategory');

        $categories = $fetcher->all();

        return $this->render('app/card/categories/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ZapCategory');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.categories');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/categories/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ZapCategory $zapCategory
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ZapCategory $zapCategory, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCategory');

        $command = Edit\Command::fromEntity($zapCategory);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.categories');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/categories/edit.html.twig', [
            'form' => $form->createView(),
            'zapCategory' => $zapCategory
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ZapCategoryRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, ZapCategoryRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ZapCategory');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $zapCategory = $repository->get($id);

            if (count($zapCategory->getGroups()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить категорию, содержащую группы']);
            }

            $repository->removeSort($zapCategory->getNumber());
            $em->remove($zapCategory);
            $flusher->flush();
            $data['message'] = 'Категория товаров удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param ZapCategoryRepository $repository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function sort(int $id, ZapCategoryRepository $repository, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $opt = $repository->get($id);

            $oldSort = $opt->getNumber();
            $newSort = $sorter->getNewSort($opt->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $repository->removeSort($oldSort);
                $repository->addSort($newSort);

                $opt->changeNumber($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

<?php

namespace App\Controller\User;

use App\Model\EntityNotFoundException;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\Model\User\Entity\ShopPayType\ShopPayType;
use App\Model\User\Entity\ShopPayType\ShopPayTypeRepository;
use App\Model\User\UseCase\Opt\Edit;
use App\Model\User\UseCase\Opt\Create;
use App\Model\Flusher;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ModelSorter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/price-list/opt", name="price.list.opt")
 */
class OptController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param OptFetcher $fetcher
     * @return Response
     */
    public function index(OptFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Opt');

        $opts = $fetcher->all();

        return $this->render('app/users/opt/index.html.twig', [
            'opts' => $opts,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Opt');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('price.list.opt');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/opt/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Opt $opt
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Opt $opt, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Opt');

        $command = Edit\Command::fromEntity($opt);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('price.list.opt');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/opt/edit.html.twig', [
            'form' => $form->createView(),
            'opt' => $opt
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param OptRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, OptRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Opt');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $opt = $repository->get($id);

            if (count($opt->getUsers()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить колонку прайс-листа, используемую у клиентов']);
            }

            $repository->removeSort($opt->getNumber());
            $em->remove($opt);
            $flusher->flush();
            $data['message'] = 'Колонка прайс-листа удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param OptRepository $repository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function sort(int $id, OptRepository $repository, Flusher $flusher, ModelSorter $sorter): Response
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

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param OptRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, OptRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $opt = $repository->get($request->query->getInt('id'));
            $opt->hide();
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
     * @param OptRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, OptRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $opt = $repository->get($request->query->getInt('id'));
            $opt->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

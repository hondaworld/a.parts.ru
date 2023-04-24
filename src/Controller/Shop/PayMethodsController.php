<?php


namespace App\Controller\Shop;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\PayMethod\PayMethod;
use App\Model\Shop\Entity\PayMethod\PayMethodRepository;
use App\Model\Shop\UseCase\PayMethod\Create;
use App\Model\Shop\UseCase\PayMethod\Edit;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Shop\PayMethodFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ModelSorter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/pay-methods", name="shop.payMethods")
 */
class PayMethodsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param PayMethodFetcher $fetcher
     * @return Response
     */
    public function index(PayMethodFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PayMethod');

        $all = $fetcher->all();

        return $this->render('app/shop/payMethods/index.html.twig', [
            'all' => $all,
            'table_checkable' => true,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'PayMethod');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.payMethods');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/payMethods/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param PayMethod $payMethod
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(PayMethod $payMethod, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'PayMethod');

        $command = Edit\Command::fromEntity($payMethod);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.payMethods');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/payMethods/edit.html.twig', [
            'form' => $form->createView(),
            'payMethod' => $payMethod
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param PayMethod $payMethod
     * @param PayMethodRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(PayMethod $payMethod, PayMethodRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'PayMethod');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
//        if (count($delivery->getPrices()) > 0) {
//            return $this->json(['code' => 500, 'message' => 'Невозможно удалить поставщика, содержащего прайс-листы']);
//        } else {
            try {
                $repository->removeSort($payMethod->getNumber());
                $em->remove($payMethod);
                $flusher->flush();
                $data['message'] = 'Способ оплаты удален';

            } catch (EntityNotFoundException $e) {
                return $this->json(['code' => 404, 'message' => $e->getMessage()]);
            }
//        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param PayMethodRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, PayMethodRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'PayMethod');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $payMethod = $repository->get($request->query->getInt('id'));
            $payMethod->hide();
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
     * @param PayMethodRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, PayMethodRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'PayMethod');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $payMethod = $repository->get($request->query->getInt('id'));
            $payMethod->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param PayMethodRepository $repository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     */
    public function sort(int $id, PayMethodRepository $repository, Flusher $flusher, ModelSorter $sorter): Response
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
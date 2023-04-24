<?php


namespace App\Controller\Shop;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\Reseller\Reseller;
use App\Model\Shop\Entity\Reseller\ResellerRepository;
use App\Model\Shop\UseCase\Reseller\Create;
use App\Model\Shop\UseCase\Reseller\Edit;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Shop\ResellerFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/resellers", name="shop.resellers")
 */
class RsellersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ResellerFetcher $fetcher
     * @return Response
     */
    public function index(ResellerFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Reseller');

        $all = $fetcher->all();

        return $this->render('app/shop/reseller/index.html.twig', [
            'all' => $all,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Reseller');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.resellers');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/reseller/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Reseller $reseller
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Reseller $reseller, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Reseller');

        $command = Edit\Command::fromEntity($reseller);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.resellers');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/reseller/edit.html.twig', [
            'form' => $form->createView(),
            'reseller' => $reseller
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Reseller $reseller
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Reseller $reseller, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Reseller');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        if (count($reseller->getExpenseDocuments()) > 0) {
            return $this->json(['code' => 500, 'message' => 'Невозможно удалить реселлера, имеющего заказы']);
        } else {
            try {
                $em->remove($reseller);
                $flusher->flush();
                $data['message'] = 'Реселлер удалена';

            } catch (EntityNotFoundException $e) {
                return $this->json(['code' => 404, 'message' => $e->getMessage()]);
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ResellerRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ResellerRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'Reseller');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $reseller = $repository->get($request->query->getInt('id'));
            $reseller->hide();
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
     * @param ResellerRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ResellerRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'Reseller');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $reseller = $repository->get($request->query->getInt('id'));
            $reseller->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
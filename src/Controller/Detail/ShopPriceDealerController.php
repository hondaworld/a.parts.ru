<?php


namespace App\Controller\Detail;


use App\Model\Detail\Entity\Dealer\ShopPriceDealer;
use App\Model\Detail\Entity\Dealer\ShopPriceDealerRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Detail\UseCase\Dealer\Create;
use App\Model\Detail\UseCase\Dealer\Edit;
use App\Model\Detail\UseCase\Dealer\Upload;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Detail\ShopPriceDealerFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/dealer/prices", name="dealer.prices")
 */
class ShopPriceDealerController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ShopPriceDealerFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ShopPriceDealerFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopPriceDealer');

        $settings = $settings->get('shopPriceDealer');

        $filter = new Filter\ShopPriceDealer\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ShopPriceDealer\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/detail/dealer/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopPriceDealer');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('dealer.prices', ['page' => $request->getSession()->get('page/shopPriceDealer') ?: 1]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/dealer/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ShopPriceDealer $shopPriceDealer
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ShopPriceDealer $shopPriceDealer, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopPriceDealer');
        $command = Edit\Command::fromEntity($shopPriceDealer);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('dealer.prices', ['page' => $request->getSession()->get('page/shopPriceDealer') ?: 1]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/dealer/edit.html.twig', [
            'form' => $form->createView(),
            'dealer' => $shopPriceDealer
        ]);
    }

    /**
     * @Route("/upload", name=".upload")
     * @param Request $request
     * @param Upload\Handler $handler
     * @return Response
     */
    public function upload(Request $request, Upload\Handler $handler): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopPriceDealer');

        $command = new Upload\Command();

        $form = $this->createForm(Upload\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $data = $handler->handle($command, $file);
                    if ($data['done'] > 0) $this->addFlash('success', 'Загружено ' . $data['done'] . ' цен');
                    if ($data['update'] > 0) $this->addFlash('info', 'Обновлено ' . $data['update'] . ' цен');
                }
                return $this->redirectToRoute('dealer.prices', ['page' => $request->getSession()->get('page/shopPriceDealer') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/dealer/upload.html.twig', [
            'form' => $form->createView(),
            'edit' => 'upload1'
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ShopPriceDealer $shopPriceDealer
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ShopPriceDealer $shopPriceDealer, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopPriceDealer');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($shopPriceDealer);
            $flusher->flush();
            $data['message'] = 'Дилерская цена удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param ShopPriceDealerRepository $shopPriceDealerRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, ShopPriceDealerRepository $shopPriceDealerRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopPriceDealer');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $weight = $shopPriceDealerRepository->get($request->query->getInt('id'));
            $em->remove($weight);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
<?php


namespace App\Controller\Detail;


use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\Detail\Entity\Zamena\ShopZamenaRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Detail\UseCase\Zamena\Create;
use App\Model\Detail\UseCase\Zamena\Upload1;
use App\Model\Detail\UseCase\Zamena\Upload2;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Detail\ShopZamenaFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/zamena", name="zamena")
 */
class ShopZamenaController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ShopZamenaFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ShopZamenaFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopZamena');

        $settings = $settings->get('shopZamena');

        $filter = new Filter\ShopZamena\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ShopZamena\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/detail/zamena/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopZamena');
        $command = new Create\Command();

        if ($request->get('number')) $command->number = $request->get('number');

        $manager = $managerRepository->get($this->getUser()->getId());

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $manager);
                return $this->redirectToRoute('zamena', ['page' => $request->getSession()->get('page/shopZamena') ?: 1]);

            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/zamena/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/upload", name=".upload")
     * @param Request $request
     * @return Response
     */
    public function upload(Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopZamena');

        return $this->render('app/detail/zamena/upload.html.twig', [
            'edit' => ''
        ]);
    }

    /**
     * @Route("/upload1", name=".upload1")
     * @param Request $request
     * @param Upload1\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function upload1(Request $request, Upload1\Handler $handler, ManagerRepository $managerRepository): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopZamena');

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new Upload1\Command();

        $form = $this->createForm(Upload1\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $data = $handler->handle($command, $file, $manager);
                    $this->addFlash('success', 'Загружено ' . $data['done'] . ' замен');
                    $this->addFlash('info', 'Существует ' . $data['exist'] . ' замен');
                }
                return $this->redirectToRoute('zamena', ['page' => $request->getSession()->get('page/shopZamena') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/zamena/upload.html.twig', [
            'form' => $form->createView(),
            'edit' => 'upload1'
        ]);
    }

    /**
     * @Route("/upload2", name=".upload2")
     * @param Request $request
     * @param Upload2\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function upload2(Request $request, Upload2\Handler $handler, ManagerRepository $managerRepository): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopZamena');

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new Upload2\Command();

        $form = $this->createForm(Upload2\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $data = $handler->handle($command, $file, $manager);
                    $this->addFlash('success', 'Загружено ' . $data['done'] . ' замен');
                    $this->addFlash('info', 'Существует ' . $data['exist'] . ' замен');
                    $this->addFlash('error', 'Не найдено производителей ' . $data['notFound']);
                }
                return $this->redirectToRoute('zamena', ['page' => $request->getSession()->get('page/shopZamena') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/zamena/upload.html.twig', [
            'form' => $form->createView(),
            'edit' => 'upload2'
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ShopZamena $shopZamena
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ShopZamena $shopZamena, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopZamena');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($shopZamena);
            $flusher->flush();
            $data['message'] = 'Замена удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param ShopZamenaRepository $shopZamenaRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, ShopZamenaRepository $shopZamenaRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopZamena');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $shopZamena = $shopZamenaRepository->get($request->query->getInt('id'));
            $em->remove($shopZamena);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
<?php


namespace App\Controller\Detail;


use App\Model\Detail\Entity\Weight\Weight;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Detail\UseCase\Weight\Create;
use App\Model\Detail\UseCase\Weight\Edit;
use App\Model\Detail\UseCase\Weight\Upload1;
use App\Model\Detail\UseCase\Weight\Upload2;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Detail\WeightFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/weights", name="weights")
 */
class WeightsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param WeightFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, WeightFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Weight');

        $settings = $settings->get('weight');

        $filter = new Filter\Weight\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Weight\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/detail/weights/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Weight');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('weights', ['page' => $request->getSession()->get('page/weight') ?: 1]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/weights/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Weight $weight
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Weight $weight, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Weight');
        $command = Edit\Command::fromEntity($weight);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('weights', ['page' => $request->getSession()->get('page/weight') ?: 1]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/weights/edit.html.twig', [
            'form' => $form->createView(),
            'weight' => $weight
        ]);
    }

    /**
     * @Route("/upload", name=".upload")
     * @return Response
     */
    public function upload(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Weight');

        return $this->render('app/detail/weights/upload.html.twig', [
            'edit' => ''
        ]);
    }

    /**
     * @Route("/upload1", name=".upload1")
     * @param Request $request
     * @param Upload1\Handler $handler
     * @return Response
     */
    public function upload1(Request $request, Upload1\Handler $handler): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Weight');

        $command = new Upload1\Command();

        $form = $this->createForm(Upload1\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $data = $handler->handle($command, $file);
                    $this->addFlash('success', 'Загружено ' . $data['done'] . ' весов');
                    $this->addFlash('info', 'Существует ' . $data['exist'] . ' весов');
                }
                return $this->redirectToRoute('weights', ['page' => $request->getSession()->get('page/weight') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/weights/upload.html.twig', [
            'form' => $form->createView(),
            'edit' => 'upload1'
        ]);
    }

    /**
     * @Route("/upload2", name=".upload2")
     * @param Request $request
     * @param Upload2\Handler $handler
     * @return Response
     */
    public function upload2(Request $request, Upload2\Handler $handler): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Weight');

        $command = new Upload2\Command();

        $form = $this->createForm(Upload2\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $data = $handler->handle($command, $file);
                    $this->addFlash('success', 'Загружено ' . $data['done'] . ' весов');
                    $this->addFlash('info', 'Существует ' . $data['exist'] . ' весов');
                    $this->addFlash('error', 'Не найдено весов ' . $data['notFound']);
                }
                return $this->redirectToRoute('weights', ['page' => $request->getSession()->get('page/weight') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/weights/upload.html.twig', [
            'form' => $form->createView(),
            'edit' => 'upload2'
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Weight $weight
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Weight $weight, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Weight');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($weight);
            $flusher->flush();
            $data['message'] = 'Вес удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param WeightRepository $weightRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, WeightRepository $weightRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Weight');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $weight = $weightRepository->get($request->query->getInt('id'));
            $em->remove($weight);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
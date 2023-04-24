<?php


namespace App\Controller\Auto;


use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Auto\Entity\Auto\AutoRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Auto\UseCase\Auto\Create;
use App\Model\Auto\UseCase\Auto\Edit;
use App\ReadModel\Auto\AutoFetcher;
use App\ReadModel\Auto\Filter;
use App\ReadModel\User\UserFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/user-auto", name="userAuto")
 */
class AutoController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param AutoFetcher $fetcher
     * @param ManagerSettings $settings
     * @param UserFetcher $userFetcher
     * @return Response
     */
    public function index(Request $request, AutoFetcher $fetcher, ManagerSettings $settings, UserFetcher $userFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Auto');

        $settings = $settings->get('autos');

        $filter = new Filter\Auto\Filter();
        $filter->inPage = isset($settings['inPage']) ? $settings['inPage'] : $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Auto\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $items = $pagination->getItems();
        foreach ($items as &$item) {
            $item['users'] = $userFetcher->findByAuto($item['autoID']);
        }
        $pagination->setItems($items);

        return $this->render('app/auto/auto/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Auto');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('userAuto', ['page' => $request->getSession()->get('page/autos') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/auto/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Auto $auto
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Auto $auto, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Auto');
        $command = Edit\Command::fromEntity($auto);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('userAuto', ['page' => $request->getSession()->get('page/autos') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/auto/edit.html.twig', [
            'form' => $form->createView(),
            'auto' => $auto
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Auto $auto
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Auto $auto, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($auto);
            $flusher->flush();
            $data['message'] = 'Автомобиль удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param AutoRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, AutoRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $auto = $repository->get($request->query->getInt('id'));
            $auto->hide();
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
     * @param AutoRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, AutoRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $auto = $repository->get($request->query->getInt('id'));
            $auto->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
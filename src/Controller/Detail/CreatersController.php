<?php


namespace App\Controller\Detail;


use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Detail\UseCase\Creater\Create;
use App\Model\Detail\UseCase\Creater\Edit;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Detail\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/creaters", name="creaters")
 */
class CreatersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param CreaterFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, CreaterFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Creater');

        $settings = $settings->get('creaters');

        $filter = new Filter\Creater\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Creater\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/detail/creaters/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Creater');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $creater = $handler->handle($command);
                if ($this->isGranted(StandartActionsVoter::SHOW, 'Creater')) {
                    return $this->redirectToRoute('creaters.show', ['id' => $creater->getId()]);
                } else {
                    return $this->redirectToRoute('creaters', ['page' => $request->getSession()->get('page/creaters') ?: 1]);
                }

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/creaters/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param Creater $creater
     * @return Response
     */
    public function show(Creater $creater): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'Creater');

        return $this->render('app/detail/creaters/show.html.twig', [
            'creater' => $creater,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Creater $creater
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Creater $creater, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Creater');

        $command = Edit\Command::fromEntity($creater);

        $form = $this->createForm(Edit\Form::class, $command, ['attr' => ['isAdmin' => $this->getUser()->isAdmin()]]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('creaters.show', ['id' => $creater->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/creaters/show.html.twig', [
            'form' => $form->createView(),
            'creater' => $creater,
            'edit' => 'main',
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param CreaterRepository $createrRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, CreaterRepository $createrRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Creater');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $creater = $createrRepository->get($id);
            $em->remove($creater);
            $flusher->flush();
            $data['message'] = 'Производитель удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param CreaterRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, CreaterRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $creater = $repository->get($request->query->getInt('id'));
            $creater->hide();
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
     * @param CreaterRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, CreaterRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $creater = $repository->get($request->query->getInt('id'));
            $creater->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
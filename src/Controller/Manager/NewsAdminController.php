<?php


namespace App\Controller\Manager;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\NewsAdmin\NewsAdmin;
use App\Model\Manager\Entity\NewsAdmin\NewsAdminRepository;
use App\Model\Manager\UseCase\NewsAdmin\Create;
use App\Model\Manager\UseCase\NewsAdmin\Edit;
use App\ReadModel\Manager\NewsAdminFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/news-admin", name="news-admin")
 */
class NewsAdminController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param NewsAdminFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, NewsAdminFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'NewsAdmin');

        $settings = $settings->get('newsAdmin');

        $pagination = $fetcher->all($request->query->getInt('page', 1), $settings);

        return $this->render('app/managers/newsAdmin/index.html.twig', [
            'pagination' => $pagination,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'NewsAdmin');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('news-admin', ['page' => $request->getSession()->get('page/newsAdmin') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/newsAdmin/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param NewsAdmin $newsAdmin
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(NewsAdmin $newsAdmin, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'NewsAdmin');

        $command = Edit\Command::fromEntity($newsAdmin);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('news-admin', ['page' => $request->getSession()->get('page/newsAdmin') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/newsAdmin/edit.html.twig', [
            'newsAdmin' => $newsAdmin,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param NewsAdminRepository $newsAdminRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, NewsAdminRepository $newsAdminRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'NewsAdmin');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $newsAdmin = $newsAdminRepository->get($id);

            $em->remove($newsAdmin);
            $flusher->flush();
            $data['message'] = 'Новость удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param NewsAdminRepository $firms
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, NewsAdminRepository $firms, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $newsAdmin = $firms->get($request->query->getInt('id'));
            $newsAdmin->hide();
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
     * @param NewsAdminRepository $firms
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, NewsAdminRepository $firms, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $newsAdmin = $firms->get($request->query->getInt('id'));
            $newsAdmin->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
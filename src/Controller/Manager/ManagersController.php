<?php


namespace App\Controller\Manager;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Manager\UseCase\Manager\Create;
use App\Model\Manager\UseCase\Manager\Edit;
use App\ReadModel\Manager\Filter;
use App\ReadModel\Manager\ManagerFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\FileUploader;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/managers", name="managers")
 */
class ManagersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ManagerFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ManagerFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Manager');

        $settings = $settings->get('managers');

        $filter = new Filter\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/managers/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Manager');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers', ['page' => $request->getSession()->get('page/managers') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Manager $manager
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Manager $manager, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Manager');

        $command = Edit\Command::fromManager($manager, $this->getParameter('manager_photo_www'));

        $form = $this->createForm(Edit\Form::class, $command, ['attr' => ['isAdmin' => $this->getUser()->isAdmin()]]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            dump($form->isValid());
            try {
                $handler->checkLogin($command);

                $photo = $form->get('photo')->getData();
                if (!$this->getUser()->isAdmin() && !$this->isGranted('ROLE_SUPER_ADMIN')) $command->isAdmin = $manager->getIsAdmin();

                if ($photo) {
                    $fileUploader = new FileUploader($this->getParameter('manager_photo_directory'));
                    $newFilename = $fileUploader->upload($photo);
                    $fileUploader->resize($newFilename, Manager::PHOTO_MAX_WIDTH, Manager::PHOTO_MAX_HEIGHT);
                    $fileUploader->delete($manager->getPhoto());
                    $command->photo = $newFilename;
                } else {
                    $command->photo = $manager->getPhoto();
                }

                $handler->handle($command);
                return $this->redirectToRoute('managers', ['page' => $request->getSession()->get('page/managers') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/edit.html.twig', [
            'form' => $form->createView(),
            'manager' => $manager
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param ManagerRepository $managers
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, Request $request, ManagerRepository $managers, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Manager');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $manager = $managers->get($id);
            if ($manager->getPhoto()) {
                $fileUploader = new FileUploader($this->getParameter('manager_photo_directory'));
                $fileUploader->delete($manager->getPhoto());
            }

            $manager->clearDirectorFirms();
            $manager->clearBuhgalterFirms();
            $manager->clearUsers();
            $manager->clearBalanceHistory();

            $em->remove($manager);
            $flusher->flush();
            $data['message'] = 'Менеджер удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/photo/delete", name=".photo.delete")
     * @param Manager $manager
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function photoDelete(Manager $manager, Request $request, Flusher $flusher): Response
    {
        $photo = $manager->getPhoto();

        if ($photo) {
            $fileUploader = new FileUploader($this->getParameter('manager_photo_directory'));
            $fileUploader->delete($photo);

            $manager->deletePhoto();

            $flusher->flush();
        }

        return $this->json([]);
    }
}
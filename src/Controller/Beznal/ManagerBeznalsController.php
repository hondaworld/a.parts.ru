<?php


namespace App\Controller\Beznal;


use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Beznal\Entity\Beznal\BeznalRepository;
use App\Model\Beznal\UseCase\Beznal\Create;
use App\Model\Beznal\UseCase\Beznal\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Beznal\BankFetcher;
use App\ReadModel\Beznal\BeznalFetcher;
use App\Security\Voter\Manager\ManagerVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/managers/beznals", name="managers.beznals")
 */
class ManagerBeznalsController extends AbstractController
{

    /**
     * @Route("/{managerID}/", name="")
     * @param Manager $manager
     * @param BeznalFetcher $fetcher
     * @return Response
     */
    public function index(Manager $manager, BeznalFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_BEZNALS, $manager);

        $beznals = $fetcher->allByManager($manager);

        return $this->render('app/beznals/managers/index.html.twig', [
            'manager' => $manager,
            'beznals' => $beznals,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{managerID}/create", name=".create")
     * @param Manager $manager
     * @param Request $request
     * @return Response
     */
    public function create(Manager $manager, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_BEZNALS_CHANGE, $manager);

        $command = new Create\Command($manager);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.beznals', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/managers/create.html.twig', [
            'manager' => $manager,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{managerID}/{id}/edit", name=".edit")
     * @ParamConverter("manager", options={"id" = "managerID"})
     * @param Manager $manager
     * @param Beznal $beznal
     * @param Request $request
     * @param Edit\Handler $handler
     * @param BankFetcher $bankFetcher
     * @return Response
     */
    public function edit(Manager $manager, Beznal $beznal, Request $request, Edit\Handler $handler, BankFetcher $bankFetcher): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_BEZNALS_CHANGE, $manager);

        $command = Edit\Command::fromBeznal($beznal, $bankFetcher);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.beznals', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/managers/edit.html.twig', [
            'manager' => $manager,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{managerID}/{id}/delete", name=".delete")
     * @ParamConverter("manager", options={"id" = "managerID"})
     * @param Manager $manager
     * @param int $id
     * @param Request $request
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Manager $manager, int $id, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_BEZNALS_CHANGE, $manager);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $beznal = $beznals->get($id);
            $em->remove($beznal);
            $flusher->flush();
            $data['message'] = 'Реквизит менеджера удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{managerID}/hide", name=".hide")
     * @param Manager $manager
     * @param Request $request
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Manager $manager, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $beznal = $beznals->get($request->query->getInt('id'));
            if ($beznal->isMain() == 1) {
                $data = ['code' => 500, 'message' => 'Невозможно скрыть основной реквизит'];
            } else {
                $beznal->hide();
                $flusher->flush();
                $data['action'] = 'hide';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{managerID}/unHide", name=".unHide")
     * @param Manager $manager
     * @param Request $request
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Manager $manager, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $beznal = $beznals->get($request->query->getInt('id'));
            $beznal->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
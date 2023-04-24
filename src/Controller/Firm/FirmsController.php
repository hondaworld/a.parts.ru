<?php


namespace App\Controller\Firm;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Firm\UseCase\Firm\Create;
use App\Model\Firm\UseCase\Firm\Edit;
use App\Model\Firm\UseCase\Firm\Others;
use App\ReadModel\Firm\FirmFetcher;
use App\Security\Voter\Firm\FirmVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/firms", name="firms")
 */
class FirmsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param FirmFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(FirmFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Firm');

        $settings = $settings->get('firms');

        $pagination = $fetcher->all($settings);

        return $this->render('app/firms/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Firm');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms', ['page' => $request->getSession()->get('page/firms') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Firm $firm
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Firm $firm, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Firm');

        $command = Edit\Command::fromEntity($firm);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/edit.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/others", name=".others")
     * @param Firm $firm
     * @param Request $request
     * @param Others\Handler $handler
     * @return Response
     */
    public function others(Firm $firm, Request $request, Others\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_OTHERS, $firm);

        $command = Others\Command::fromEntity($firm);

        $form = $this->createForm(Others\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Данные сохранены');
                return $this->redirectToRoute('firms.others', ['id' => $firm->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/others.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param FirmRepository $firms
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, FirmRepository $firms, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Firm');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $firm = $firms->get($id);

            if (count($firm->getBalanceHistory()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить организацию из истории клиентов']);
            } else {

                $em->remove($firm);
                $flusher->flush();
                $data['message'] = 'Организация удалена';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param FirmRepository $firms
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, FirmRepository $firms, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firm = $firms->get($request->query->getInt('id'));
            $firm->hide();
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
     * @param FirmRepository $firms
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, FirmRepository $firms, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firm = $firms->get($request->query->getInt('id'));
            $firm->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
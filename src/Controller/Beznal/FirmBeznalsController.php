<?php


namespace App\Controller\Beznal;


use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Beznal\Entity\Beznal\BeznalRepository;
use App\Model\Beznal\UseCase\Beznal\Create;
use App\Model\Beznal\UseCase\Beznal\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Firm\Entity\Firm\Firm;
use App\ReadModel\Beznal\BankFetcher;
use App\ReadModel\Beznal\BeznalFetcher;
use App\Security\Voter\Firm\FirmVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/firms/beznals", name="firms.beznals")
 */
class FirmBeznalsController extends AbstractController
{

    /**
     * @Route("/{firmID}/", name="")
     * @param Firm $firm
     * @param BeznalFetcher $fetcher
     * @return Response
     */
    public function index(Firm $firm, BeznalFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_BEZNALS, $firm);

        $beznals = $fetcher->allByFirm($firm);

        return $this->render('app/beznals/firms/index.html.twig', [
            'firm' => $firm,
            'beznals' => $beznals,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{firmID}/create", name=".create")
     * @param Firm $firm
     * @param Request $request
     * @return Response
     */
    public function create(Firm $firm, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_BEZNALS_CHANGE, $firm);

        $command = new Create\Command($firm);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.beznals', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/firms/create.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/edit", name=".edit")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param Beznal $beznal
     * @param Request $request
     * @param Edit\Handler $handler
     * @param BankFetcher $bankFetcher
     * @return Response
     */
    public function edit(Firm $firm, Beznal $beznal, Request $request, Edit\Handler $handler, BankFetcher $bankFetcher): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_BEZNALS_CHANGE, $firm);

        $command = Edit\Command::fromBeznal($beznal, $bankFetcher);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                //return $this->redirectToRoute('firms.beznals', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/firms/edit.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/delete", name=".delete")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param int $id
     * @param Request $request
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Firm $firm, int $id, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(FirmVoter::FIRM_BEZNALS_CHANGE, $firm);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $beznal = $beznals->get($id);
            $em->remove($beznal);
            $flusher->flush();
            $data['message'] = 'Реквизит организации удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{firmID}/hide", name=".hide")
     * @param Firm $firm
     * @param Request $request
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Firm $firm, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
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
     * @Route("/{firmID}/unHide", name=".unHide")
     * @param Firm $firm
     * @param Request $request
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Firm $firm, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
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
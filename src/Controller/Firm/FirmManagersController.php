<?php


namespace App\Controller\Firm;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use App\Model\Firm\Entity\OrgGroup\OrgGroupRepository;
use App\Model\Firm\Entity\OrgJob\OrgJobRepository;
use App\Model\Flusher;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\UseCase\ManagerFirm\Create;
use App\Model\Firm\UseCase\ManagerFirm\Edit;
use App\ReadModel\Firm\ManagerFirmFetcher;
use App\Security\Voter\Firm\FirmVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/firms/managers", name="firms.managers")
 */
class FirmManagersController extends AbstractController
{
    /**
     * @Route("/{firmID}/", name="")
     * @param Firm $firm
     * @param Request $request
     * @param ManagerFirmFetcher $fetcher
     * @return Response
     */
    public function index(Firm $firm, Request $request, ManagerFirmFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_MANAGERS, $firm);

        $managers = $fetcher->allByFirm($firm);

        return $this->render('app/firms/firmManagers/index.html.twig', [
            'managers' => $managers,
            'firm' => $firm,
        ]);
    }

    /**
     * @Route("/{firmID}/create", name=".create")
     * @param Firm $firm
     * @param Request $request
     * @param Create\Handler $handler
     * @param OrgGroupRepository $orgGroupRepository
     * @param OrgJobRepository $orgJobRepository
     * @return Response
     */
    public function create(Firm $firm, Request $request, Create\Handler $handler, OrgGroupRepository $orgGroupRepository, OrgJobRepository $orgJobRepository): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_MANAGERS, $firm);

        $command = new Create\Command($firm, $orgGroupRepository->getMain(), $orgJobRepository->getMain());

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.managers', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/firmManagers/create.html.twig', [
            'form' => $form->createView(),
            'firm' => $firm,
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/edit", name=".edit")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param ManagerFirm $managerFirm
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Firm $firm, ManagerFirm $managerFirm, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_MANAGERS, $firm);

        $command = Edit\Command::fromEntity($managerFirm);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.managers', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/firmManagers/edit.html.twig', [
            'manager' => $managerFirm,
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/delete", name=".delete")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param ManagerFirm $managerFirm
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Firm $firm, ManagerFirm $managerFirm, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(FirmVoter::FIRM_MANAGERS, $firm);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($managerFirm);
            $flusher->flush();
            $data['message'] = 'Сотрудник удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
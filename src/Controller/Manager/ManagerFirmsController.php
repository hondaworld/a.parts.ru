<?php


namespace App\Controller\Manager;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use App\Model\Firm\Entity\OrgGroup\OrgGroupRepository;
use App\Model\Firm\Entity\OrgJob\OrgJobRepository;
use App\Model\Flusher;
use App\Model\Firm\UseCase\ManagerFirm\Create;
use App\Model\Firm\UseCase\ManagerFirm\Edit;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Firm\ManagerFirmFetcher;
use App\Security\Voter\Manager\ManagerVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/managers/firms", name="managers.firms")
 */
class ManagerFirmsController extends AbstractController
{
    /**
     * @Route("/{managerID}/", name="")
     * @param Manager $manager
     * @param Request $request
     * @param ManagerFirmFetcher $fetcher
     * @return Response
     */
    public function index(Manager $manager, Request $request, ManagerFirmFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_FIRMS, $manager);

        $firms = $fetcher->allByManager($manager);

        return $this->render('app/managers/managerFirms/index.html.twig', [
            'firms' => $firms,
            'manager' => $manager,
        ]);
    }

    /**
     * @Route("/{managerID}/create", name=".create")
     * @param Manager $manager
     * @param Request $request
     * @param Create\Handler $handler
     * @param OrgGroupRepository $orgGroupRepository
     * @param OrgJobRepository $orgJobRepository
     * @return Response
     */
    public function create(Manager $manager, Request $request, Create\Handler $handler, OrgGroupRepository $orgGroupRepository, OrgJobRepository $orgJobRepository): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_FIRMS, $manager);

        $command = new Create\Command($manager, $orgGroupRepository->getMain(), $orgJobRepository->getMain());

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.firms', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/managerFirms/create.html.twig', [
            'form' => $form->createView(),
            'manager' => $manager,
        ]);
    }

    /**
     * @Route("/{managerID}/{id}/edit", name=".edit")
     * @ParamConverter("manager", options={"id" = "managerID"})
     * @param Manager $manager
     * @param ManagerFirm $managerFirm
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Manager $manager, ManagerFirm $managerFirm, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_FIRMS, $manager);

        $command = Edit\Command::fromEntity($managerFirm);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.firms', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/managerFirms/edit.html.twig', [
            'firm' => $managerFirm,
            'manager' => $manager,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{managerID}/{id}/delete", name=".delete")
     * @ParamConverter("manager", options={"id" = "managerID"})
     * @param Manager $manager
     * @param ManagerFirm $managerFirm
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Manager $manager, ManagerFirm $managerFirm, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_FIRMS, $manager);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($managerFirm);
            $flusher->flush();
            $data['message'] = 'Организация удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
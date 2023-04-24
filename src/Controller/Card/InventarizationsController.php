<?php


namespace App\Controller\Card;


use App\Model\Card\Entity\Inventarization\Inventarization;
use App\Model\Card\Entity\Inventarization\InventarizationRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Card\UseCase\Inventarization\Create;
use App\Model\Card\UseCase\Inventarization\Edit;
use App\ReadModel\Card\InventarizationFetcher;
use App\ReadModel\Beznal\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/inventarizations", name="inventarizations")
 */
class InventarizationsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param InventarizationFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, InventarizationFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $settings = $settings->get('inventarization');

        $pagination = $fetcher->all(
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/card/inventarizations/index.html.twig', [
            'pagination' => $pagination,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('inventarizations', ['page' => $request->getSession()->get('page/inventarization') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/inventarizations/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Inventarization $inventarization
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Inventarization $inventarization, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $command = Edit\Command::fromEntity($inventarization);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('inventarizations', ['page' => $request->getSession()->get('page/inventarization') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/inventarizations/edit.html.twig', [
            'inventarization' => $inventarization,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param InventarizationRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, InventarizationRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $inventarization = $repository->get($id);
            $em->remove($inventarization);
            $flusher->flush();
            $data['message'] = 'Инвентаризация удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
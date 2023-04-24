<?php


namespace App\Controller\Card;


use App\Model\Card\Entity\Auto\ZapCardAuto;
use App\Model\Card\Entity\Auto\ZapCardAutoRepository;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Card\UseCase\Auto\Create;
use App\ReadModel\Card\ZapCardAutoFetcher;
use App\ReadModel\Detail\Filter;
use App\Security\Voter\StandartActionsVoter;
use Doctrine\DBAL\Exception;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/parts/auto", name="card.parts.auto")
 */
class ZapCardAutoController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapCard $zapCard
     * @param ZapCardAutoFetcher $fetcher
     * @return Response
     * @throws Exception
     */
    public function index(ZapCard $zapCard, ZapCardAutoFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');

        $all = $fetcher->allByZapCard($zapCard->getId());

        return $this->render('app/card/auto/index.html.twig', [
            'all' => $all,
            'zapCard' => $zapCard,
            'table_checkable' => true
        ]);
    }

    /**
     * @Route("/{id}/create", name=".create")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(ZapCard $zapCard, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        $command = new Create\Command($zapCard);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.auto', ['id' => $zapCard->getId()]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/auto/create.html.twig', [
            'form' => $form->createView(),
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{zapCardID}/{id}/delete", name=".delete")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCardAuto $zapCardAuto
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapCardAuto $zapCardAuto, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($zapCardAuto);
            $flusher->flush();
            $data['message'] = 'Применимость удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{zapCardID}/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param ZapCardAutoRepository $zapCardAutoRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, ZapCardAutoRepository $zapCardAutoRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $shopZamena = $zapCardAutoRepository->get($request->query->getInt('id'));
            $em->remove($shopZamena);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
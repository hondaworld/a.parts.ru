<?php


namespace App\Controller\Card;


use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\Detail\Entity\Zamena\ShopZamenaRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Card\UseCase\Zamena\Create;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Detail\ShopZamenaFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/parts/zamena", name="card.parts.zamena")
 */
class ShopZamenaController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ShopZamenaFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(ZapCard $zapCard, Request $request, ShopZamenaFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopZamena');

        $all = $fetcher->allByNumberAndCreater($zapCard->getNumber()->getValue(), $zapCard->getCreater()->getId());

        return $this->render('app/card/zamena/index.html.twig', [
            'all' => $all,
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/create", name=".create")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function create(ZapCard $zapCard, Request $request, Create\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopZamena');
        $command = new Create\Command($zapCard);

        $manager = $managerRepository->get($this->getUser()->getId());

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $manager);
                return $this->redirectToRoute('card.parts.zamena', ['id' => $zapCard->getId()]);

            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/zamena/create.html.twig', [
            'form' => $form->createView(),
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{zapCardID}/{id}/delete", name=".delete")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ShopZamena $shopZamena
     * @param Request $request
     * @param ShopZamenaRepository $shopZamenaRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapCard $zapCard, ShopZamena $shopZamena, Request $request, ShopZamenaRepository $shopZamenaRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopZamena');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($shopZamena);
            $flusher->flush();
            $data['message'] = 'Замена удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
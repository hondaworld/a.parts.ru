<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\UseCase\Card\Number;
use App\Model\Card\UseCase\Card\Name;
use App\Model\Card\UseCase\Card\Dop;
use App\Model\Card\UseCase\Card\Description;
use App\Model\Card\UseCase\Card\Weight;
use App\Model\Card\UseCase\Card\Manager;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\Flusher;
use App\ReadModel\Card\AbcFetcher;
use App\ReadModel\Card\Filter;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\Card\ZapCardManagerVoter;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/card/parts", name="card.parts")
 */
class ZapCardController extends AbstractController
{
    /**
     * @Route("/{id}/number", name=".number")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Number\Handler $handler
     * @param WeightRepository $weightRepository
     * @param AbcFetcher $abcFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function number(ZapCard $zapCard, Request $request, Number\Handler $handler, WeightRepository $weightRepository, AbcFetcher $abcFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $weight = $weightRepository->findByZapCard($zapCard);

        $abc = $abcFetcher->all();

        $zapSklads = $zapSkladFetcher->assoc();

        $command = Number\Command::fromEntity($zapCard);

        $form = $this->createForm(Number\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/parts/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'number',
            'weight' => $weight,
            'abc' => $abc,
            'zapSklads' => $zapSklads,
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/name", name=".name")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Name\Handler $handler
     * @param WeightRepository $weightRepository
     * @param AbcFetcher $abcFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function name(ZapCard $zapCard, Request $request, Name\Handler $handler, WeightRepository $weightRepository, AbcFetcher $abcFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $weight = $weightRepository->findByZapCard($zapCard);

        $abc = $abcFetcher->all();

        $zapSklads = $zapSkladFetcher->assoc();

        $command = Name\Command::fromEntity($zapCard);

        $form = $this->createForm(Name\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/parts/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'name',
            'weight' => $weight,
            'abc' => $abc,
            'zapSklads' => $zapSklads,
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/dop", name=".dop")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Dop\Handler $handler
     * @param WeightRepository $weightRepository
     * @param AbcFetcher $abcFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function dop(ZapCard $zapCard, Request $request, Dop\Handler $handler, WeightRepository $weightRepository, AbcFetcher $abcFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $weight = $weightRepository->findByZapCard($zapCard);

        $abc = $abcFetcher->all();

        $zapSklads = $zapSkladFetcher->assoc();

        $command = Dop\Command::fromEntity($zapCard);

        $form = $this->createForm(Dop\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/parts/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'dop',
            'weight' => $weight,
            'abc' => $abc,
            'zapSklads' => $zapSklads,
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/description", name=".description")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Description\Handler $handler
     * @param WeightRepository $weightRepository
     * @param AbcFetcher $abcFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function description(ZapCard $zapCard, Request $request, Description\Handler $handler, WeightRepository $weightRepository, AbcFetcher $abcFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $weight = $weightRepository->findByZapCard($zapCard);

        $abc = $abcFetcher->all();

        $zapSklads = $zapSkladFetcher->assoc();

        $command = Description\Command::fromEntity($zapCard);

        $form = $this->createForm(Description\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/parts/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'description',
            'weight' => $weight,
            'abc' => $abc,
            'zapSklads' => $zapSklads,
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/manager", name=".manager")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Manager\Handler $handler
     * @param WeightRepository $weightRepository
     * @param AbcFetcher $abcFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function manager(ZapCard $zapCard, Request $request, Manager\Handler $handler, WeightRepository $weightRepository, AbcFetcher $abcFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(ZapCardManagerVoter::ZAP_CARD_MANAGER_CHANGE, $zapCard);

        $weight = $weightRepository->findByZapCard($zapCard);

        $abc = $abcFetcher->all();

        $zapSklads = $zapSkladFetcher->assoc();

        $command = Manager\Command::fromEntity($zapCard);

        $form = $this->createForm(Manager\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/parts/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'manager',
            'weight' => $weight,
            'abc' => $abc,
            'zapSklads' => $zapSklads,
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/weight", name=".weight")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Weight\Handler $handler
     * @param WeightRepository $weightRepository
     * @param Flusher $flusher
     * @param AbcFetcher $abcFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function weight(ZapCard $zapCard, Request $request, Weight\Handler $handler, WeightRepository $weightRepository, Flusher $flusher, AbcFetcher $abcFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $weight = $weightRepository->findByZapCard($zapCard);

        $abc = $abcFetcher->all();

        $zapSklads = $zapSkladFetcher->assoc();

        $command = Weight\Command::fromEntity($zapCard, $weight);

        $form = $this->createForm(Weight\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($command->weight) {
                    $handler->handle($command, $weight);
                } else {
                    if ($weight) {
                        $em = $this->getDoctrine()->getManager();
                        $em->remove($weight);
                        $flusher->flush();
                    }
                }
                return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/parts/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'weight',
            'weight' => $weight,
            'abc' => $abc,
            'zapSklads' => $zapSklads,
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ZapCard $zapCard
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapCard $zapCard, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ZapCard');

        $zapCard->delete();

        $flusher->flush();

        return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
    }

    /**
     * @Route("/{id}/restore", name=".restore")
     * @param ZapCard $zapCard
     * @param Flusher $flusher
     * @return Response
     */
    public function restore(ZapCard $zapCard, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ZapCard');

        $zapCard->restore();

        $flusher->flush();

        return $this->redirectToRoute('card.parts.show', ['id' => $zapCard->getId()]);
    }
}

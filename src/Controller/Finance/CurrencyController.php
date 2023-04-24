<?php

namespace App\Controller\Finance;

use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Finance\UseCase\Currency\Edit;
use App\Model\Finance\UseCase\Currency\Create;
use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\ReadModel\Finance\CurrencyFetcher;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/finance/currency", name="currency")
 */
class CurrencyController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param CurrencyFetcher $fetcher
     * @return Response
     */
    public function index(CurrencyFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Currency');

        $currencies = $fetcher->all();

        return $this->render('app/finance/currency/index.html.twig', [
            'currencies' => $currencies,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Currency');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('currency');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/currency/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Currency $currency
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Currency $currency, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Currency');

        $command = Edit\Command::fromCurrency($currency);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('currency');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/currency/edit.html.twig', [
            'form' => $form->createView(),
            'currency' => $currency
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param CurrencyRepository $currencies
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, Request $request, CurrencyRepository $currencies, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Currency');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $currency = $currencies->get($id);
            $em->remove($currency);
            $flusher->flush();
            $data['message'] = 'Валюта удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param CurrencyRepository $currencies
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, CurrencyRepository $currencies, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $currency = $currencies->get($request->query->getInt('id'));
            $currency->hide();
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
     * @param CurrencyRepository $currencies
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, CurrencyRepository $currencies, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $currency = $currencies->get($request->query->getInt('id'));
            $currency->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

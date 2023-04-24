<?php


namespace App\Controller\Provider;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Provider\UseCase\Provider\BalanceHistory\Create;
use App\Model\Provider\UseCase\Provider\BalanceHistory\Edit;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Firm\FirmBalanceHistoryFetcher;
use App\ReadModel\Firm\Filter;
use App\ReadModel\User\UserFetcher;
use App\Security\Voter\Provider\ProviderBalanceVoter;
use App\Service\ManagerSettings;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers/balance/history", name="providers.balance.history")
 */
class ProviderBalanceHistoryController extends AbstractController
{

    /**
     * @Route("/{providerID}/", name="")
     * @param Provider $provider
     * @param Request $request
     * @param FirmBalanceHistoryFetcher $fetcher
     * @param UserFetcher $userFetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(Provider $provider, Request $request, FirmBalanceHistoryFetcher $fetcher, UserFetcher $userFetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(ProviderBalanceVoter::PROVIDER_BALANCE, 'Provider');

        $settings = $settings->get('providerBalanceHistory');

        $filter = new Filter\ProviderBalanceHistory\Filter($provider->getId());
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ProviderBalanceHistory\Form::class, $filter);
        $form->handleRequest($request);

        $act = new Filter\ProviderBalanceAct\Filter($provider->getId());
        $formAct = $this->createForm(Filter\ProviderBalanceAct\Form::class, $act);

        $isPrint = $request->query->getBoolean('isPrint');

        $pagination = $fetcher->allByProvider(
            $provider,
            $filter,
            $request->query->getInt('page', 1),
            $settings,
            $isPrint
        );

        $balances = $fetcher->balanceByProvider($provider);
        $users = $userFetcher->assocFromBalanceByProviderID($provider->getId());

        if ($isPrint) {
            return $this->render('app/providers/balanceHistory/print.html.twig', [
                'provider' => $provider,
                'pagination' => $pagination
            ]);
        }

        return $this->render('app/providers/balanceHistory/index.html.twig', [
            'provider' => $provider,
            'pagination' => $pagination,
            'balances' => $balances,
            'users' => $users,
            'filter' => $form->createView(),
            'act' => $formAct->createView()
        ]);
    }

    /**
     * @Route("/{providerID}/act", name=".act")
     * @param Provider $provider
     * @param Request $request
     * @param FirmBalanceHistoryFetcher $fetcher
     * @param UserRepository $userFetcher
     * @param FirmRepository $firmRepository
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function act(Provider $provider, Request $request, FirmBalanceHistoryFetcher $fetcher, UserRepository $userFetcher, FirmRepository $firmRepository): Response
    {
        $this->denyAccessUnlessGranted(ProviderBalanceVoter::PROVIDER_BALANCE, 'Provider');

        $act = new Filter\ProviderBalanceAct\Filter($provider->getId());
        $act->firmID = $request->query->get('form')['firmID'];
        $act->userID = $request->query->get('form')['userID'];
        $act->dateofadded['date_from'] = $request->query->get('form')['dateofadded']['date_from'];
        $act->dateofadded['date_till'] = $request->query->get('form')['dateofadded']['date_till'];
        if (!$act->dateofadded['date_from'] || !$act->dateofadded['date_till']) {
            $act->DefaultDate();
        }

        $date_from = new \DateTime($act->dateofadded['date_from']);
        $date_till = new \DateTime($act->dateofadded['date_till']);

        $user = $userFetcher->get($act->userID);
        $firm = $firmRepository->get($act->firmID);

        $balance = $fetcher->act($provider, $act);

        return $this->render('app/providers/balanceHistory/act.html.twig', [
            'balance' => $balance,
            'user' => $user,
            'firm' => $firm,
            'date_from' => $date_from,
            'date_till' => $date_till
        ]);
    }

    /**
     * @Route("/{providerID}/create", name=".create")
     * @param Provider $provider
     * @param Request $request
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function create(Provider $provider, Request $request, Create\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(ProviderBalanceVoter::PROVIDER_BALANCE_CHANGE, 'Provider');

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $provider, $manager);
                return $this->redirectToRoute('providers.balance.history', ['providerID' => $provider->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/balanceHistory/create.html.twig', [
            'provider' => $provider,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{providerID}/{id}/edit", name=".edit")
     * @ParamConverter("provider", options={"id" = "providerID"})
     * @param Provider $provider
     * @param FirmBalanceHistory $firmBalanceHistory
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Provider $provider, FirmBalanceHistory $firmBalanceHistory, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ProviderBalanceVoter::PROVIDER_BALANCE_CHANGE, 'Provider');

        $command = Edit\Command::fromEntity($firmBalanceHistory);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.balance.history', ['providerID' => $provider->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/balanceHistory/edit.html.twig', [
            'provider' => $provider,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{providerID}/{id}/delete", name=".delete")
     * @ParamConverter("provider", options={"id" = "providerID"})
     * @param Provider $provider
     * @param FirmBalanceHistory $firmBalanceHistory
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Provider $provider, FirmBalanceHistory $firmBalanceHistory, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ProviderBalanceVoter::PROVIDER_BALANCE_CHANGE, 'Provider');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($firmBalanceHistory);
            $flusher->flush();
            $data['message'] = 'Запись удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
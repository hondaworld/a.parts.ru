<?php


namespace App\Controller\Income;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\UseCase\Document\Unpack;
use App\Model\Income\UseCase\Document\UnpackSum;
use App\Model\Income\UseCase\Document\Weight;
use App\Model\Income\UseCase\Document\QuantityUnPack;
use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Income\Filter;
use App\Security\Voter\Income\IncomeVoter;
use Doctrine\DBAL\Exception;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/income/unpack", name="income.unpack")
 */
class IncomeUnpackController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @return Response
     */
    public function incomeUnpack(): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_UNPACK, 'Income');

        $command = new Unpack\Command();
        $form = $this->createForm(Unpack\Form::class, $command);

        return $this->render('app/income/unpack/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/check", name=".check")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param Unpack\Handler $handler
     * @return Response
     */
    public function incomeUnpackCheck(Request $request, ValidatorInterface $validator, Unpack\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_UNPACK, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new Unpack\Command();
        $command->providerID = $request->request->get('form')['providerID'];

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                if ($handler->handle($command))
                    $data['redirectToUrl'] = $this->generateUrl('income.unpack.scan', ['id' => $command->providerID]);
                else
                    $data['redirectToUrl'] = $this->generateUrl('income.unpack.sum', ['id' => $command->providerID]);
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            $data['messages'] = [];
            foreach ($errors as $error) {
                $data['messages'][] = $error->getMessage();
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sum", name=".sum")
     * @param Provider $provider
     * @param Request $request
     * @param UnpackSum\Handler $handler
     * @return Response
     */
    public function incomeUnpackSum(Provider $provider, Request $request, UnpackSum\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_UNPACK, 'Income');

        $command = new UnpackSum\Command();
        $form = $this->createForm(UnpackSum\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $provider);
                return $this->redirectToRoute('income.unpack.scan', ['id' => $provider->getId()]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/income/unpackSum/index.html.twig', [
            'provider' => $provider,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/scan", name=".scan")
     * @param Provider $provider
     * @param Request $request
     * @param IncomeRepository $incomeRepository
     * @param WeightFetcher $weightFetcher
     * @return Response
     * @throws Exception
     */
    public function incomeUnpackScan(Provider $provider, Request $request, IncomeRepository $incomeRepository, WeightFetcher $weightFetcher): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_UNPACK, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $incomes = $incomeRepository->findByProviderIncomeInWarehouse($provider);

        $arr = [];
        $quantity = 0;
        $quantityUnPack = 0;
        $searchNumber = $request->query->get('number') ? (new DetailNumber($request->query->get('number'))) : '';

        if ($searchNumber != '') {
            foreach ($incomes as $income) {
                if ($searchNumber->isEqual($income->getZapCard()->getNumber())) {

                    if (!isset($arr[$income->getZapCard()->getId()]['zapCard'])) {
                        $arr[$income->getZapCard()->getId()]['zapCard'] = $income->getZapCard();
                        $weightCommand = Weight\Command::fromZapCard($income->getZapCard());
                        if (!isset($arr[$income->getZapCard()->getId()]['weights'])) {
                            $arr[$income->getZapCard()->getId()]['weights'] = $weightFetcher->oneByNumberAndCreater($income->getZapCard()->getNumber()->getValue(), $income->getZapCard()->getCreater()->getId());
                            if ($arr[$income->getZapCard()->getId()]['weights']) $weightCommand->weight = $arr[$income->getZapCard()->getId()]['weights']['weight'];
                        }
                        $formWeight = $this->createForm(Weight\Form::class, $weightCommand);
                        $arr[$income->getZapCard()->getId()]['formWeight'] = $formWeight->createView();

                    }

                    $qCommand = new QuantityUnPack\Command();
                    $formQ = $this->createForm(QuantityUnPack\Form::class, $qCommand);
                    $arr[$income->getZapCard()->getId()]['incomeForms'][$income->getId()] = $formQ->createView();

                    $arr[$income->getZapCard()->getId()]['incomes'][] = $income;
                    $arr[$income->getZapCard()->getId()]['quantity'] = ($arr[$income->getZapCard()->getId()]['quantity'] ?? 0) + $income->getQuantity();
                    $quantity += $income->getQuantity();
                    $arr[$income->getZapCard()->getId()]['quantityUnPack'] = ($arr[$income->getZapCard()->getId()]['quantityUnPack'] ?? 0) + $income->getQuantityUnPack();
                    $quantityUnPack += $income->getQuantityUnPack();

                }
            }
        }

        return $this->render('app/income/scan/index.html.twig', [
            'provider' => $provider,
            'incomes' => $incomes,
            'arr' => $arr,
            'searchNumber' => $searchNumber == '' ? '' : $searchNumber->getValue(),
            'isUnpackNumber' => $quantity - $quantityUnPack == 0,
            'zap_card_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('zap_card_photo') . '/'
//            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{providerID}/{id}/weight", name=".weight")
     * @ParamConverter("provider", options={"id" = "providerID"})
     * @param Provider $provider
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Weight\Handler $handler
     * @return Response
     */
    public function weight(Provider $provider, ZapCard $zapCard, Request $request, Weight\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_UNPACK, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $command = Weight\Command::fromZapCard($zapCard);
        $form = $this->createForm(Weight\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $provider);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
        return $this->redirectToRoute('income.unpack.scan', ['id' => $provider->getId(), 'number' => $request->query->get('searchNumber') ?? '']);
    }

    /**
     * @Route("/{providerID}/{id}/quantityUnPack", name=".quantityUnPack")
     * @ParamConverter("provider", options={"id" = "providerID"})
     * @param Provider $provider
     * @param Income $income
     * @param Request $request
     * @param QuantityUnPack\Handler $handler
     * @return Response
     */
    public function quantityUnPack(Provider $provider, Income $income, Request $request, QuantityUnPack\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_UNPACK, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $command = new QuantityUnPack\Command();
        $form = $this->createForm(QuantityUnPack\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $income);
                return $this->redirectToRoute('income.unpack.scan', ['id' => $provider->getId(), 'number' => $request->query->get('searchNumber') ?? '', 'scan' => 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('income.unpack.scan', ['id' => $provider->getId(), 'number' => $request->query->get('searchNumber') ?? '']);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Income $income
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Income $income, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_UNPACK, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $income->removeQuantityUnPack();
        $flusher->flush();

        $data['reload'] = true;

        return $this->json($data);
    }

}
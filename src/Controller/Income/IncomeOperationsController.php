<?php


namespace App\Controller\Income;


use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\UseCase\Income\Status;
use App\Model\Income\UseCase\Income\ZapSklad;
use App\Model\Income\UseCase\Document\Create;
use App\Model\Income\UseCase\Document\CreateReturn;
use App\Model\Income\UseCase\Document\CreateWriteOff;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Income\Filter;
use App\Security\Voter\Income\IncomeVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/income/change", name="income.change")
 */
class IncomeOperationsController extends AbstractController
{
    /**
     * @Route("/status", name=".status")
     * @return Response
     */
    public function statusForm(): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_STATUS, 'Income');

        $command = new Status\Command();
        $form = $this->createForm(Status\Form::class, $command);

        return $this->render('app/income/status/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/status/update", name=".status.update")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param Status\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function statusUpdate(Request $request, ValidatorInterface $validator, Status\Handler $handler, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_STATUS, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new Status\Command();
        $command->status = $request->request->get('form')['status'];
        $command->dateofinplan = $request->request->get('form')['dateofinplan'];
        $command->deleteReasonID = $request->request->get('form')['deleteReasonID'];
        $command->cols = $request->request->get('cols');

        $errors = $validator->validate($command);
        if (count($errors) == 0) {
            try {
                $messages = $handler->handle($command, $manager);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }
                $data['reload'] = true;

            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($errors as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/document", name=".document")
     * @param FirmFetcher $firmFetcher
     * @return Response
     */
    public function incomeDocumentForm(FirmFetcher $firmFetcher): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_INSERT_DOCUMENT, 'Income');

        $command = new Create\Command($firmFetcher);
        $form = $this->createForm(Create\Form::class, $command);

        return $this->render('app/income/document/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/document/create", name=".document.create")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param FirmFetcher $firmFetcher
     * @return Response
     */
    public function incomeDocumentCreate(Request $request, ValidatorInterface $validator, Create\Handler $handler, ManagerRepository $managerRepository, FirmFetcher $firmFetcher): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_INSERT_DOCUMENT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new Create\Command($firmFetcher);
        $command->document_prefix = $request->request->get('form')['document_prefix'];
        $command->document_sufix = $request->request->get('form')['document_sufix'];
        $command->firmID = $request->request->get('form')['firmID'];
        $command->providerID = $request->request->get('form')['providerID'];
        $command->user_contactID = $request->request->get('form')['user_contactID'];
        $command->osn_nakladnaya = $request->request->get('form')['osn_nakladnaya'];
        $command->osn_schet = $request->request->get('form')['osn_schet'];
        $command->is_priceZak = $request->request->get('form')['is_priceZak'] ?? false;
        $command->balance = $request->request->get('form')['balance'];
        $command->balance_nds = $request->request->get('form')['balance_nds'];
        $command->description = $request->request->get('form')['description'];

        $errors = $validator->validate($command);
        if (count($errors) == 0) {
            try {
                $messages = $handler->handle($command, $manager);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }
                $data['reload'] = true;

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
     * @Route("/documentReturn", name=".documentReturn")
     * @param Request $request
     * @param FirmFetcher $firmFetcher
     * @param IncomeRepository $incomeRepository
     * @return Response
     */
    public function incomeDocumentReturnForm(Request $request, FirmFetcher $firmFetcher, IncomeRepository $incomeRepository): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_RETURN_DOCUMENT, 'Income');

        $returning = $request->query->get('returning');
        if ($returning != '') {
            $arReturning = explode(',', $returning);
        } else {
            $arReturning = [];
        }
        $incomes = [];
        if ($arReturning) {
            $incomes = $incomeRepository->findByIDs($arReturning);
        }

        $incomeSklads = [];
        foreach ($incomes as $income) {
            foreach ($income->getSklads() as $sklad) {
                $incomeSklads[$sklad->getId()] = 0;
            }
        }

        $command = new CreateReturn\Command($firmFetcher, $incomeSklads);
        $form = $this->createForm(CreateReturn\Form::class, $command);

        return $this->render('app/income/documentReturn/form.html.twig', [
            'form' => $form->createView(),
            'incomes' => $incomes,
            'returning' => $returning
        ]);
    }

    /**
     * @Route("/documentReturn/create", name=".documentReturn.create")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param CreateReturn\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param FirmFetcher $firmFetcher
     * @param IncomeRepository $incomeRepository
     * @return Response
     */
    public function incomeDocumentReturnCreate(Request $request, ValidatorInterface $validator, CreateReturn\Handler $handler, ManagerRepository $managerRepository, FirmFetcher $firmFetcher, IncomeRepository $incomeRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_RETURN_DOCUMENT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $returning = $request->query->get('returning');
        if ($returning != '') {
            $arReturning = explode(',', $returning);
        } else {
            $arReturning = [];
        }
        $incomes = [];
        if ($arReturning) {
            $incomes = $incomeRepository->findByIDs($arReturning);
        }

        $incomeSklads = [];
        foreach ($incomes as $income) {
            foreach ($income->getSklads() as $sklad) {
                $incomeSklads[$sklad->getId()] = $request->request->get('form')['incomeSklad_' . $sklad->getId()] ?? 0;
            }
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new CreateReturn\Command($firmFetcher, $incomeSklads);
        $command->document_prefix = $request->request->get('form')['document_prefix'];
        $command->document_sufix = $request->request->get('form')['document_sufix'];
        $command->firmID = $request->request->get('form')['firmID'];
        $command->providerID = $request->request->get('form')['providerID'];
        $command->returning_reason = $request->request->get('form')['returning_reason'];

        $errors = $validator->validate($command);
        if (count($errors) == 0) {
            try {
                $messages = $handler->handle($command, $manager);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }
                $data['reload'] = true;

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
     * @Route("/documentWriteOff", name=".documentWriteOff")
     * @param Request $request
     * @param IncomeRepository $incomeRepository
     * @return Response
     */
    public function incomeDocumentWriteOffForm(Request $request, IncomeRepository $incomeRepository): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_WRITE_OFF_DOCUMENT, 'Income');

        $returning = $request->query->get('returning');
        if ($returning != '') {
            $arReturning = explode(',', $returning);
        } else {
            $arReturning = [];
        }
        $incomes = [];
        if ($arReturning) {
            $incomes = $incomeRepository->findByIDs($arReturning);
        }

        $incomeSklads = [];
        foreach ($incomes as $income) {
            foreach ($income->getSklads() as $sklad) {
                $incomeSklads[$sklad->getId()] = 0;
            }
        }

        $command = new CreateWriteOff\Command($incomeSklads);
        $form = $this->createForm(CreateWriteOff\Form::class, $command);

        return $this->render('app/income/documentWriteOff/form.html.twig', [
            'form' => $form->createView(),
            'incomes' => $incomes,
            'returning' => $returning
        ]);
    }

    /**
     * @Route("/documentWriteOff/create", name=".documentWriteOff.create")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param CreateWriteOff\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param IncomeRepository $incomeRepository
     * @return Response
     */
    public function incomeDocumentWriteOffCreate(Request $request, ValidatorInterface $validator, CreateWriteOff\Handler $handler, ManagerRepository $managerRepository, IncomeRepository $incomeRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_WRITE_OFF_DOCUMENT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }


        $returning = $request->query->get('returning');
        if ($returning != '') {
            $arReturning = explode(',', $returning);
        } else {
            $arReturning = [];
        }
        $incomes = [];
        if ($arReturning) {
            $incomes = $incomeRepository->findByIDs($arReturning);
        }

        $incomeSklads = [];
        foreach ($incomes as $income) {
            foreach ($income->getSklads() as $sklad) {
                $incomeSklads[$sklad->getId()] = $request->request->get('form')['incomeSklad_' . $sklad->getId()] ?? 0;
            }
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new CreateWriteOff\Command($incomeSklads);
        $command->document_prefix = $request->request->get('form')['document_prefix'];
        $command->document_sufix = $request->request->get('form')['document_sufix'];
        $command->returning_reason = $request->request->get('form')['returning_reason'];

        $errors = $validator->validate($command);
        if (count($errors) == 0) {
            try {
                $messages = $handler->handle($command, $manager);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }
                $data['reload'] = true;

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
     * @Route("/zapSklad", name=".zapSklad")
     * @return Response
     */
    public function zapSkladForm(): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_ZAP_SKLAD, 'Income');

        $command = new ZapSklad\Command();
        $form = $this->createForm(ZapSklad\Form::class, $command);

        return $this->render('app/income/zapSklad/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/zapSklad/update", name=".zapSklad.update")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ZapSklad\Handler $handler
     * @return Response
     */
    public function zapSkladUpdate(Request $request, ValidatorInterface $validator, ZapSklad\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_ZAP_SKLAD, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

//        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new ZapSklad\Command();
        $command->zapSkladID = $request->request->get('form')['zapSkladID'];
        $command->cols = $request->request->get('cols');

        $errors = $validator->validate($command);
        if (count($errors) == 0) {
            try {
                $handler->handle($command);

                $data['reload'] = true;

            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($errors as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }
}
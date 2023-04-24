<?php


namespace App\Controller\Firm;


use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Firm\Service\SchetEmailService;
use App\Model\Firm\Service\SchetPsbUrlGenerator;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Firm\UseCase\Schet\DocumentNum;
use App\Model\Firm\UseCase\Schet\Pay;
use App\Model\Firm\UseCase\Schet\PayUrl;
use App\Model\Firm\UseCase\Schet\Cancel;
use App\Model\Firm\UseCase\Schet\UserContact;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\ReadModel\Firm\SchetFetcher;
use App\ReadModel\Firm\Filter;
use App\Security\Voter\Firm\SchetVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use App\Service\Sms\SmsSender;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/schets", name="schets")
 */
class SchetsController extends AbstractController
{

    /**
     * @Route("/", name="")
     * @param Request $request
     * @param SchetFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, SchetFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Schet');

        $settings = $settings->get('schet');

        $filter = new Filter\Schet\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Schet\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/firms/schet/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'statuses' => Schet::STATUSES
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param Schet $schet
     * @return Response
     */
    public function show(Schet $schet): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'Schet');

        return $this->render('app/firms/schet/show.html.twig', [
            'schet' => $schet,
            'statuses' => Schet::STATUSES,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/documentNum", name=".documentNum")
     * @param Schet $schet
     * @param Request $request
     * @param DocumentNum\Handler $handler
     * @return Response
     */
    public function documentNum(Schet $schet, Request $request, DocumentNum\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(SchetVoter::SCHET_CHANGE_DOCUMENT, 'Schet');

        $command = DocumentNum\Command::fromEntity($schet);

        $form = $this->createForm(DocumentNum\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('schets.show', ['id' => $schet->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/schet/show.html.twig', [
            'schet' => $schet,
            'edit' => 'documentNum',
            'statuses' => Schet::STATUSES,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/pay", name=".pay")
     * @param Schet $schet
     * @param Request $request
     * @param Pay\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function pay(Schet $schet, Request $request, Pay\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(SchetVoter::SCHET_PAY, 'Schet');

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = Pay\Command::fromEntity($schet);

        $form = $this->createForm(Pay\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $messages = $handler->handle($command, $manager);
                foreach ($messages as $message) {
                    $this->addFlash($message['type'], $message['message']);
                }
                return $this->redirectToRoute('schets.show', ['id' => $schet->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/schet/show.html.twig', [
            'schet' => $schet,
            'edit' => 'pay',
            'statuses' => Schet::STATUSES,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/payUrl", name=".payUrl")
     * @param Schet $schet
     * @param Request $request
     * @param PayUrl\Handler $handler
     * @return Response
     */
    public function payUrl(Schet $schet, Request $request, PayUrl\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(SchetVoter::SCHET_PAY, 'Schet');

        $command = PayUrl\Command::fromEntity($schet);

        $form = $this->createForm(PayUrl\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('schets.show', ['id' => $schet->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/schet/show.html.twig', [
            'schet' => $schet,
            'edit' => 'payUrl',
            'statuses' => Schet::STATUSES,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/generatePayUrl", name=".generatePayUrl")
     * @param Schet $schet
     * @param Request $request
     * @param SchetPsbUrlGenerator $schetPsbUrlGenerator
     * @param PayUrl\Handler $handler
     * @return Response
     */
    public function generatePayUrl(Schet $schet, Request $request, SchetPsbUrlGenerator $schetPsbUrlGenerator, PayUrl\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(SchetVoter::SCHET_PAY, 'Schet');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $result = $schetPsbUrlGenerator->getUrl($schet);
            if (isset($result['ERROR'])) {
                throw new DomainException($result['ERROR']);
            }
            dump($result);

//            $data['message'] = 'E-mail отправлен на ' . $email;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }


        return $this->json($data);



//        $command = PayUrl\Command::fromEntity($schet);
//
//        $form = $this->createForm(PayUrl\Form::class, $command);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            try {
//                $handler->handle($command);
//                return $this->redirectToRoute('schets.show', ['id' => $schet->getId()]);
//            } catch (DomainException $e) {
//                $this->addFlash('error', $e->getMessage());
//            }
//        }

        return $this->redirectToRoute('schets.show', ['id' => $schet->getId()]);

//        return $this->render('app/home.html.twig', [
//
//        ]);
    }

    /**
     * @Route("/{id}/cancel", name=".cancel")
     * @param Schet $schet
     * @param Request $request
     * @param Cancel\Handler $handler
     * @return Response
     */
    public function cancel(Schet $schet, Request $request, Cancel\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(SchetVoter::SCHET_CANCEL, 'Schet');

        $command = new Cancel\Command($schet->getId());

        $form = $this->createForm(Cancel\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Счет отклонен');
                return $this->redirectToRoute('schets.show', ['id' => $schet->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/schet/show.html.twig', [
            'schet' => $schet,
            'edit' => 'cancel',
            'statuses' => Schet::STATUSES,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/userContact", name=".userContact")
     * @param Schet $schet
     * @param Request $request
     * @param UserContact\Handler $handler
     * @return Response
     */
    public function userContact(Schet $schet, Request $request, UserContact\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Schet');

        $command = UserContact\Command::fromEntity($schet);

        $form = $this->createForm(UserContact\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('schets.show', ['id' => $schet->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/schet/show.html.twig', [
            'schet' => $schet,
            'edit' => 'userContact',
            'statuses' => Schet::STATUSES,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/smsPayUrl", name=".smsPayUrl")
     * @param Schet $schet
     * @param Request $request
     * @param ManagerRepository $managerRepository
     * @param SmsSender $smsSender
     * @param TemplateRepository $templateRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function smsPayUrl(Schet $schet, Request $request, ManagerRepository $managerRepository, SmsSender $smsSender, TemplateRepository $templateRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(SchetVoter::SCHET_SMS_SEND, 'Schet');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        try {
            if (!$schet->getUser()->isSms()) {
                throw new DomainException('Пользователь запретил отсылать SMS');
            }
            if ($schet->getPayUrl() == '') {
                throw new DomainException('Ссылка не задана');
            }
            $smsSender->sendFromParts($manager, $schet->getUser(), ($templateRepository->get(Template::SMS_SCHET_PAY_URL))->getText([
                'url' => $schet->getPayUrl()
            ]));
            $flusher->flush();

            $data['message'] = 'SMS отправлено на ' . $schet->getUser()->getPhonemob();
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }


        return $this->json($data);
    }

    /**
     * @Route("/{id}/mail", name=".mail")
     * @param Schet $schet
     * @param SchetEmailService $schetEmailService
     * @return Response
     */
    public function mail(Schet $schet, SchetEmailService $schetEmailService): Response
    {
        try {
            $this->denyAccessUnlessGranted(SchetVoter::SCHET_EMAIL_SEND, 'Schet');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $email = $schetEmailService->emailWithUrl($schet);
            $data['message'] = 'E-mail отправлен на ' . $email;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }


        return $this->json($data);
    }

    /**
     * @Route("/{id}/mailPdf", name=".mailPdf")
     * @param Schet $schet
     * @param SchetEmailService $schetEmailService
     * @return Response
     */
    public function mailPdf(Schet $schet, SchetEmailService $schetEmailService): Response
    {
        try {
            $this->denyAccessUnlessGranted(SchetVoter::SCHET_EMAIL_SEND, 'Schet');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $email = $schetEmailService->emailWithPdf($schet);
            $data['message'] = 'E-mail отправлен на ' . $email;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }


        return $this->json($data);
    }
}
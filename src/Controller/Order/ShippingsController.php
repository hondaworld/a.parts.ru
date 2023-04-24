<?php

namespace App\Controller\Order;

use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\Shipping\Shipping;
use App\Model\Expense\Entity\Shipping\ShippingRepository;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatusRepository;
use App\Model\Expense\UseCase\Shipping\Status;
use App\Model\Expense\UseCase\Shipping\Delivery;
use App\Model\Expense\UseCase\Shipping\Attach;
use App\Model\Expense\UseCase\Shipping\Search;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\EmailStatus\UserEmailStatus;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\Order\OrderShippingFetcher;
use App\ReadModel\Order\Filter;
use App\ReadModel\Order\ShippingView;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Email\EmailSender;
use App\Service\FileUploader;
use App\Service\ManagerSettings;
use App\Service\Sms\SmsSender;
use DateTime;
use DomainException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shippings", name="shippings")
 */
class ShippingsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param OrderShippingFetcher $orderShippingFetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, OrderShippingFetcher $orderShippingFetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Shipping');

        $settings = $settings->get('shippings');

        $filter = new Filter\Shippings\Filter();
        if (!$filter->dateofadded) {
            $filter->dateofadded['date_from'] = null;
            $filter->dateofadded['date_till'] = null;
        }
        if (!$filter->dateofadded['date_from']) {
            $filter->dateofadded['date_from'] = (new DateTime())->modify('-1 month')->format('d.m.Y');
        }

        $filter->inPage = $settings['inPage'] ?? $orderShippingFetcher::PER_PAGE;

        $form = $this->createForm(Filter\Shippings\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $orderShippingFetcher->allFilter(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/orders/shippings/index.html.twig', [
            'table_checkable' => true,
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'user_shipping_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/'
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param ExpenseDocumentFetcher $expenseDocumentFetcher
     * @param ShippingStatusRepository $shippingStatusRepository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function create(Request $request, ExpenseDocumentFetcher $expenseDocumentFetcher, ShippingStatusRepository $shippingStatusRepository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Shipping');

        if ($request->query->getInt('expenseDocumentID')) {
            try {
                $expenseDocument = $expenseDocumentFetcher->get($request->query->getInt('expenseDocumentID'));
                $shippingStatus = $shippingStatusRepository->get(ShippingStatus::DOCUMENTS_DONE);
                $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);
                $flusher->flush();
                return $this->redirectToRoute('shippings.show', ['id' => $shipping->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $command = new Search\Command();
        $form = $this->createForm(Search\Form::class, $command);
        $form->handleRequest($request);

        $all = [];
        if ($command->document_num && $command->year) {
            $all = $expenseDocumentFetcher->findByDocumentNumAndYearNotShipping($command->document_num, $command->year);
        }

        return $this->render('app/orders/shippings/create.html.twig', [
            'form' => $form->createView(),
            'all' => $all,
        ]);
    }

    /**
     * @Route("/status", name=".status")
     * @return Response
     */
    public function statusForm(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');

        $command = new Status\Command();
        $form = $this->createForm(Status\Form::class, $command);

        return $this->render('app/orders/shippings/status/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/status/update", name=".status.update")
     * @param Request $request
     * @param Status\Handler $handler
     * @return Response
     */
    public function statusUpdate(Request $request, Status\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new Status\Command();
        $command->cols = $request->request->get('cols');
        $form = $this->createForm(Status\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//        $errors = $validator->validate($command);
//        if (count($errors) == 0) {
            try {
                $messages = $handler->handle($command);

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
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param Shipping $shipping
     * @param ShippingStatusRepository $shippingStatusRepository
     * @return Response
     */
    public function show(Shipping $shipping, ShippingStatusRepository $shippingStatusRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'Shipping');

        return $this->render('app/orders/shippings/show.html.twig', [
            'shipping' => $shipping,
            'shippingView' => new ShippingView($shipping),
            'statuses' => $shippingStatusRepository->allByNumber(),
            'user_shipping_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/',
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Shipping $shipping
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Shipping $shipping, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Shipping');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            foreach ($shipping->getPlaces() as $shippingPlace) {
                if ($shippingPlace->getPhoto1() != '') {
                    $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                    $fileUploader->deleteFromAdmin($shippingPlace->getPhoto1());
                }
                if ($shippingPlace->getPhoto2() != '') {
                    $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                    $fileUploader->deleteFromAdmin($shippingPlace->getPhoto2());
                }
            }
            if ($shipping->getNakladnaya() != '') {
                $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                $fileUploader->deleteFromAdmin($shipping->getNakladnaya());
            }
            $em->remove($shipping);
            $flusher->flush();
            $data['message'] = 'Отгрузка удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/status/change", name=".status.change")
     * @param Shipping $shipping
     * @param Request $request
     * @param ShippingStatusRepository $shippingStatusRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function statusChange(Shipping $shipping, Request $request, ShippingStatusRepository $shippingStatusRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $status = $shippingStatusRepository->get($request->query->getInt('status'));

            if ($status->getNumber() - $shipping->getStatus()->getNumber() != 1) {
                throw new DomainException("Статус не соответствует");
            } elseif ($status->getId() == ShippingStatus::SENT_STATUS && (!$shipping->getDeliveryTk() || $shipping->getTracknumber() == '')) {
                throw new DomainException("У отгрузки отсутствует ТК или трекинг номер");
            }
            $shipping->updateStatus($status);
            $flusher->flush();
            $data['reload'] = true;

        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/delivery", name=".delivery")
     * @param Shipping $shipping
     * @param Request $request
     * @param ShippingStatusRepository $shippingStatusRepository
     * @param Delivery\Handler $handler
     * @return Response
     */
    public function delivery(Shipping $shipping, Request $request, ShippingStatusRepository $shippingStatusRepository, Delivery\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');

        $command = Delivery\Command::fromEntity($shipping);

        $form = $this->createForm(Delivery\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shippings.show', ['id' => $shipping->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/shippings/show.html.twig', [
            'shipping' => $shipping,
            'edit' => 'delivery',
            'shippingView' => new ShippingView($shipping),
            'statuses' => $shippingStatusRepository->allByNumber(),
            'user_shipping_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/attach", name=".attach")
     * @param Shipping $shipping
     * @param Request $request
     * @param ShippingStatusRepository $shippingStatusRepository
     * @param Attach\Handler $handler
     * @return Response
     */
    public function attach(Shipping $shipping, Request $request, ShippingStatusRepository $shippingStatusRepository, Attach\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');

        $command = Attach\Command::fromEntity($shipping, $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/');

        $form = $this->createForm(Attach\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $nakladnaya = $form->get('nakladnaya')->getData();
                if ($nakladnaya) {
                    $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                    $newFilename = $fileUploader->uploadToAdminAndDelete($nakladnaya, $shipping->getNakladnaya());
                    if ($newFilename) {
                        $command->nakladnaya = $newFilename;
                        $handler->handle($command);
                        $this->addFlash('success', "Накладная загружена");
                        return $this->redirectToRoute('shippings.show', ['id' => $shipping->getId()]);
                    } else {
                        $this->addFlash('error', "Файл не загружен");
                    }
                } else {
                    $this->addFlash('error', "Файл не выбран");
                }


            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/shippings/show.html.twig', [
            'shipping' => $shipping,
            'edit' => 'attach',
            'shippingView' => new ShippingView($shipping),
            'statuses' => $shippingStatusRepository->allByNumber(),
            'user_shipping_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/attach/delete", name=".attach.delete")
     * @param Shipping $shipping
     * @param Flusher $flusher
     * @return Response
     */
    public function attachDelete(Shipping $shipping, Flusher $flusher): Response
    {
        $attach = $shipping->getNakladnaya();

        if ($attach != '') {
            $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
            $fileUploader->deleteFromAdmin($attach);
            $shipping->removeNakladnaya();

            $flusher->flush();
        }

        return $this->json([]);
    }

    /**
     * @Route("/{id}/mail", name=".mail")
     * @param Shipping $shipping
     * @param EmailSender $emailSender
     * @param SmsSender $smsSender
     * @param TemplateRepository $templateRepository
     * @param ManagerRepository $managerRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function mail(Shipping $shipping, EmailSender $emailSender, SmsSender $smsSender, TemplateRepository $templateRepository, ManagerRepository $managerRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $user = $shipping->getUser();
        try {
            $template = $templateRepository->get(Template::EMAIL_SHIPPING);
            $text = $template->getText([
                'delivery_tk_name' => $shipping->getDeliveryTk() ? $shipping->getDeliveryTk()->getName() : '',
                'tracknumber' => $shipping->getTracknumber()
            ]);

            $attaches = [];
            if ($shipping->getNakladnaya()) {
                $attaches[] = [
                    'body' => file_get_contents($this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/' . $shipping->getNakladnaya()),
                    'fileName' => 'nakladnaya' . substr($shipping->getNakladnaya(), strrpos($shipping->getNakladnaya(), '.'))
                ];
            }

            $emailSender->sendWithFullCheck($user, $template->getSubject(), $text, $attaches);
            $isEmail = true;
        } catch (DomainException $e) {
            $isEmail = false;
        }

        try {
            if (!$user->isSms()) {
                throw new DomainException('Пользователь запретил отсылать SMS');
            }
            $template = $templateRepository->get(Template::SMS_SHIPPING);
            $text = $template->getText([
                'delivery_tk_name' => $shipping->getDeliveryTk() ? $shipping->getDeliveryTk()->getName() : '',
                'tracknumber' => $shipping->getTracknumber()
            ]);

            $smsSender->sendFromParts($manager, $user, $text);
            $flusher->flush();
            $isSms = true;
        } catch (DomainException $e) {
            $isSms = false;
        }
        if ($isEmail || $isSms) {
            if ($isEmail && $isSms) {
                $data['message'] = 'E-mail отправлен на ' . $user->getEmail()->getValue() . '. SMS отправлено на ' . $user->getPhonemob() . '.';
            } elseif ($isEmail) {
                $data['message'] = 'E-mail отправлен на ' . $user->getEmail()->getValue() . '.';
            } else {
                $data['message'] = 'SMS отправлено на ' . $user->getPhonemob() . '.';
            }
        } else {
            $data = ['code' => 400, 'message' => 'Сообщение не отправлено. Проверьте e-mail и разрешения отправки на e-mail и телефон.'];
        }
        return $this->json($data);
    }
}

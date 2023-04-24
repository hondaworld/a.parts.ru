<?php

namespace App\Controller\Order;

use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Entity\Shipping\Shipping;
use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatusRepository;
use App\Model\User\Entity\User\User;
use App\Model\Expense\UseCase\ShippingPlace\Edit;
use App\Model\Expense\UseCase\ShippingPlace\Create;
use App\Model\Flusher;
use App\ReadModel\Order\ShippingView;
use App\Security\Voter\StandartActionsVoter;
use App\Service\FileUploader;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shippings/places", name="shippings.places")
 */
class ShippingsPlacesController extends AbstractController
{
    /**
     * @Route("/create/{id}", name=".create")
     * @param Shipping $shipping
     * @param Request $request
     * @param ShippingStatusRepository $shippingStatusRepository
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Shipping $shipping, Request $request, ShippingStatusRepository $shippingStatusRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $photo1 = $form->get('photo1')->getData();
                if ($photo1) {
                    $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                    $photo1Filename = $fileUploader->uploadToAdminAndDelete($photo1);
                    if ($photo1Filename) {
                        $command->photo1 = $photo1Filename;
                    }
                }
                $photo2 = $form->get('photo2')->getData();
                if ($photo2) {
                    $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                    $photo2Filename = $fileUploader->uploadToAdminAndDelete($photo2);
                    if ($photo2Filename) {
                        $command->photo2 = $photo2Filename;
                    }
                }
                $handler->handle($command, $shipping);
                return $this->redirectToRoute('shippings.show', ['id' => $shipping->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/shippings/show.html.twig', [
            'form' => $form->createView(),
            'shipping' => $shipping,
            'edit' => 'createPlace',
            'shippingView' => new ShippingView($shipping),
            'statuses' => $shippingStatusRepository->allByNumber(),
            'user_shipping_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/',
        ]);
    }

    /**
     * @Route("/{shippingID}/{id}/edit", name=".edit")
     * @ParamConverter("shipping", options={"id" = "shippingID"})
     * @param Shipping $shipping
     * @param ShippingPlace $shippingPlace
     * @param Request $request
     * @param ShippingStatusRepository $shippingStatusRepository
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Shipping $shipping, ShippingPlace $shippingPlace, Request $request, ShippingStatusRepository $shippingStatusRepository, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');

        $command = Edit\Command::fromEntity($shippingPlace, $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/');

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $photo1 = $form->get('photo1')->getData();
                if ($photo1) {
                    $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                    $photo1Filename = $fileUploader->uploadToAdminAndDelete($photo1, $shippingPlace->getPhoto1());
                    if ($photo1Filename) {
                        $command->photo1 = $photo1Filename;
                    }
                } else {
                    $command->photo1 = null;
                }
                $photo2 = $form->get('photo2')->getData();
                if ($photo2) {
                    $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                    $photo2Filename = $fileUploader->uploadToAdminAndDelete($photo2, $shippingPlace->getPhoto2());
                    if ($photo2Filename) {
                        $command->photo2 = $photo2Filename;
                    }
                } else {
                    $command->photo2 = null;
                }
                $handler->handle($command);
                return $this->redirectToRoute('shippings.show', ['id' => $shipping->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/shippings/show.html.twig', [
            'form' => $form->createView(),
            'shipping' => $shipping,
            'edit' => 'editPlace',
            'shippingView' => new ShippingView($shipping),
            'statuses' => $shippingStatusRepository->allByNumber(),
            'user_shipping_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/',
            'shippingPlace' => $shippingPlace
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ShippingPlace $shippingPlace
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ShippingPlace $shippingPlace, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Shipping');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if ($shippingPlace->getPhoto1() != '') {
                $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                $fileUploader->deleteFromAdmin($shippingPlace->getPhoto1());
            }
            if ($shippingPlace->getPhoto2() != '') {
                $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
                $fileUploader->deleteFromAdmin($shippingPlace->getPhoto2());
            }
            $em->remove($shippingPlace);
            $flusher->flush();
            $data['message'] = 'Место удалено';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/photo1/delete", name=".photo1.delete")
     * @param ShippingPlace $shippingPlace
     * @param Flusher $flusher
     * @return Response
     */
    public function photo1Delete(ShippingPlace $shippingPlace, Flusher $flusher): Response
    {
        $attach = $shippingPlace->getPhoto1();

        if ($attach != '') {
            $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
            $fileUploader->deleteFromAdmin($attach);
            $shippingPlace->removePhoto1();

            $flusher->flush();
        }

        return $this->json([]);
    }

    /**
     * @Route("/{id}/photo2/delete", name=".photo2.delete")
     * @param ShippingPlace $shippingPlace
     * @param Flusher $flusher
     * @return Response
     */
    public function photo2Delete(ShippingPlace $shippingPlace, Flusher $flusher): Response
    {
        $attach = $shippingPlace->getPhoto2();

        if ($attach != '') {
            $fileUploader = new FileUploader($this->getParameter('user_shipping_attach'));
            $fileUploader->deleteFromAdmin($attach);
            $shippingPlace->removePhoto2();

            $flusher->flush();
        }

        return $this->json([]);
    }
}

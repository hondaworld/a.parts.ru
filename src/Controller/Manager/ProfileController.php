<?php

namespace App\Controller\Manager;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\UseCase\Profile;
use App\Model\Manager\UseCase\Password;
use App\ReadModel\Contact\TownFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use App\Service\FileUploader;
use App\Service\ManagerSettings;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile", name="profile")
 */
class ProfileController extends AbstractController
{
    private ManagerFetcher $managers;

    public function __construct(ManagerFetcher $managers)
    {
        $this->managers = $managers;
    }

    /**
     * @Route("", name="")
     * @param Request $request
     * @param Profile\Handler $handler
     * @param TownFetcher $townFetcher
     * @return Response
     */
    public function request(Request $request, Profile\Handler $handler, TownFetcher $townFetcher): Response
    {
       $manager = $this->managers->get($this->getUser()->getId());

       $command = Profile\Command::fromManager($manager, $townFetcher, $this->getParameter('manager_photo_www'));

        $form = $this->createForm(Profile\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $photo = $form->get('photo')->getData();

                if ($photo) {
                    $fileUploader = new FileUploader($this->getParameter('manager_photo_directory'));
                    $newFilename = $fileUploader->upload($photo);
                    $fileUploader->resize($newFilename, Manager::PHOTO_MAX_WIDTH, Manager::PHOTO_MAX_HEIGHT);
                    $fileUploader->delete($manager->getPhoto());
                    $command->photo = $newFilename;
                } else {
                    $command->photo = $manager->getPhoto();
                }

                $handler->handle($command);
                $this->addFlash('success', "Профиль сохранен");
                return $this->redirectToRoute('profile');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/profile/form.html.twig', [
            'form' => $form->createView(),
            'manager' => $manager
        ]);
    }

    /**
     * @Route("/photo/delete", name=".photo.delete")
     * @param Flusher $flusher
     * @return Response
     */
    public function photoDelete(Flusher $flusher): Response
    {
        $manager = $this->managers->get($this->getUser()->getId());

        $photo = $manager->getPhoto();

        if ($photo) {
            $fileUploader = new FileUploader($this->getParameter('manager_photo_directory'));
            $fileUploader->delete($photo);

            $manager->deletePhoto();

            $flusher->flush();
        }

        return $this->json([]);
    }

    /**
     * @Route("/password", name=".password")
     * @param Request $request
     * @param Password\Handler $handler
     * @return Response
     */
    public function changePassword(Request $request, Password\Handler $handler): Response
    {
        $manager = $this->managers->get($this->getUser()->getId());

        $command = new Password\Command($manager->getId());

        $form = $this->createForm(Password\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {

                $handler->handle($command);
                $this->addFlash('success', "Пароль изменен");
                return $this->redirectToRoute('profile');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/managers/profile/password.html.twig', [
            'form' => $form->createView(),
            'manager' => $manager
        ]);
    }

    /**
     * @Route("/changeTheme", name=".changeTheme")
     * @param ManagerSettings $settings
     * @return Response
     */
    public function changeTheme(ManagerSettings $settings): Response
    {
        $response = $settings->changeTheme();
        return $this->json($response);
    }
}

<?php

namespace App\Controller\Contact;

use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Contact\Entity\TownRegion\TownRegionRepository;
use App\Model\Contact\Entity\TownType\TownType;
use App\Model\Contact\Entity\TownType\TownTypeRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Contact\UseCase\TownType\Create;
use App\Model\Contact\UseCase\TownType\Edit;
use App\ReadModel\Contact\TownRegionFetcher;
use App\ReadModel\Contact\TownTypeFetcher;
use \App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/town-types", name="townTypes")
 */
class TownTypesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param TownTypeFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, TownTypeFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'TownType');

        $types = $fetcher->all();

        return $this->render('app/contacts/town_types/index.html.twig', [
            'types' => $types,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'TownType');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('townTypes');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/town_types/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param TownRegion $type
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(TownType $type, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'TownType');

        $command = Edit\Command::fromType($type);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('townTypes');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/town_types/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param TownTypeRepository $types
     * @param TownRepository $towns
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, Request $request, TownTypeRepository $types, TownRepository $towns, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'TownType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $type = $types->get($id);

            if ($towns->hasByType($type)) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить тип, содержащий города']);
            } else {
                $em->remove($type);
                $flusher->flush();
                $data['message'] = 'Тип населенного пункта удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

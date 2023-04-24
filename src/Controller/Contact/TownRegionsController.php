<?php

namespace App\Controller\Contact;

use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Contact\Entity\TownRegion\TownRegionRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Contact\UseCase\TownRegion\Create;
use App\Model\Contact\UseCase\TownRegion\Edit;
use App\ReadModel\Contact\TownRegionFetcher;
use \App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/town-regions", name="townRegions")
 */
class TownRegionsController extends AbstractController
{
    /**
     * @Route("/{countryID}/", name="")
     * @param Country $country
     * @param Request $request
     * @param TownRegionFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Country $country, Request $request, TownRegionFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $settings = $settings->get('townRegions');

        $pagination = $fetcher->all(
            $country,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/contacts/regions/index.html.twig', [
            'pagination' => $pagination,
            'country' => $country,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{countryID}/create", name=".create")
     * @ParamConverter("country", options={"id" = "countryID"})
     * @param Country $country
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Country $country, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $command = new Create\Command($country);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('townRegions', ['countryID' => $country->getId(), 'page' => $request->getSession()->get('page/townRegions') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/regions/create.html.twig', [
            'form' => $form->createView(),
            'country' => $country
        ]);
    }

    /**
     * @Route("/{countryID}/{id}/edit", name=".edit")
     * @ParamConverter("country", options={"id" = "countryID"})
     * @param Country $country
     * @param TownRegion $region
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Country $country, TownRegion $region, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $command = Edit\Command::fromTownRegion($region);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('townRegions', ['countryID' => $country->getId(), 'page' => $request->getSession()->get('page/townRegions') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/regions/edit.html.twig', [
            'form' => $form->createView(),
            'country' => $country,
            'region' => $region,
        ]);
    }

    /**
     * @Route("/{countryID}/{id}/delete", name=".delete")
     * @ParamConverter("country", options={"id" = "countryID"})
     * @param Country $country
     * @param int $id
     * @param Request $request
     * @param TownRegionRepository $regions
     * @param TownRepository $towns
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(Country $country, int $id, Request $request, TownRegionRepository $regions, TownRepository $towns, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $region = $regions->get($id);

            if ($towns->hasByRegion($region)) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить регион, содержащий города']);
            } else {
                $em->remove($region);
                $flusher->flush();
                $data['message'] = 'Регион удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{countryID}/hide", name=".hide")
     * @param Request $request
     * @param TownRegionRepository $regions
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, TownRegionRepository $regions, Flusher $flusher): Response
    {
        dump(1);
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $region = $regions->get($request->query->getInt('id'));
            dump($region);
            $region->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{countryID}/unHide", name=".unHide")
     * @param Request $request
     * @param TownRegionRepository $regions
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, TownRegionRepository $regions, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $region = $regions->get($request->query->getInt('id'));
            $region->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

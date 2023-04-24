<?php

namespace App\Controller\Contact;

use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Contact\Entity\TownRegion\TownRegionRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Contact\UseCase\Country\Create;
use App\Model\Contact\UseCase\Country\Edit;
use App\ReadModel\Contact\CountryFetcher;
use \App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/countries", name="countries")
 */
class CountriesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param CountryFetcher $fetcher
     * @return Response
     */
    public function index(CountryFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $countries = $fetcher->all();

        return $this->render('app/contacts/countries/index.html.twig', [
            'countries' => $countries,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('countries');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/countries/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Country $country
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Country $country, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $command = Edit\Command::fromCountry($country);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('countries');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/countries/edit.html.twig', [
            'form' => $form->createView(),
            'country' => $country,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param CountryRepository $countries
     * @param TownRegionRepository $regions
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, Request $request, CountryRepository $countries, TownRegionRepository $regions, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $country = $countries->get($id);

            if ($regions->hasByCountry($country)) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить страну, содержащую регионы']);
            } else {
                $em->remove($country);
                $flusher->flush();
                $data['message'] = 'Страна удалена';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param CountryRepository $countries
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, CountryRepository $countries, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $country = $countries->get($request->query->getInt('id'));
            $country->hide();
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
     * @param CountryRepository $countries
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, CountryRepository $countries, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $country = $countries->get($request->query->getInt('id'));
            $country->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

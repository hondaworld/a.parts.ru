<?php

namespace App\Controller\Contact;

use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\Town\Town;
use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\ReadModel\Contact\Filter;
use App\Model\Contact\UseCase\Town\Create;
use App\Model\Contact\UseCase\Town\Edit;
use App\ReadModel\Contact\TownFetcher;
use \App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/towns", name="towns")
 */
class TownsController extends AbstractController
{
    /**
     * @Route("/{countryID}/", name="")
     * @param Country $country
     * @param Request $request
     * @param TownFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Country $country, Request $request, TownFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $settings = $settings->get('towns');

        $filter = new Filter\Towns\Filter($country);
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;
        if ($request->get('regionID')) $filter->regionID = $request->get('regionID');

        $form = $this->createForm(Filter\Towns\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $country,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/contacts/towns/index.html.twig', [
            'pagination' => $pagination,
            'country' => $country,
            'filter' => $form->createView(),
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
                return $this->redirectToRoute('towns', ['countryID' => $country->getId(), 'page' => $request->getSession()->get('page/towns') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/towns/create.html.twig', [
            'form' => $form->createView(),
            'country' => $country
        ]);
    }

    /**
     * @Route("/{countryID}/{id}/edit", name=".edit")
     * @ParamConverter("country", options={"id" = "countryID"})
     * @param Country $country
     * @param Town $town
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Country $country, Town $town, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');

        $command = Edit\Command::fromTown($town);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('towns', ['countryID' => $country->getId(), 'page' => $request->getSession()->get('page/towns') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/towns/edit.html.twig', [
            'form' => $form->createView(),
            'country' => $country,
            'town' => $town,
        ]);
    }

    /**
     * @Route("/{countryID}/{id}/delete", name=".delete")
     * @ParamConverter("country", options={"id" = "countryID"})
     * @param Country $country
     * @param int $id
     * @param Request $request
     * @param ContactRepository $contacts
     * @param TownRepository $towns
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Country $country, int $id, Request $request, ContactRepository $contacts, TownRepository $towns, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Country');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $town = $towns->get($id);

            if ($contacts->hasByTown($town)) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить город, используемый в контакте']);
            } elseif (count($town->getUsers()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить город, используемый в клиентах']);
            } else {
                $em->remove($town);
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
     * @param TownRepository $towns
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, TownRepository $towns, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $town = $towns->get($request->query->getInt('id'));
            $town->hide();
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
     * @param TownRepository $towns
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, TownRepository $towns, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $town = $towns->get($request->query->getInt('id'));
            $town->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

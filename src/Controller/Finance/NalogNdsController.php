<?php

namespace App\Controller\Finance;

use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Finance\Entity\Nalog\NalogRepository;
use App\Model\Finance\Entity\NalogNds\NalogNds;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Finance\UseCase\NalogNds\Edit;
use App\Model\Finance\UseCase\NalogNds\Create;
use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\ReadModel\Finance\CurrencyFetcher;
use App\ReadModel\Finance\NalogFetcher;
use App\ReadModel\Finance\NalogNdsFetcher;
use App\Security\Voter\StandartActionsVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/finance/nalog/nds", name="nalog.nds")
 */
class NalogNdsController extends AbstractController
{
    /**
     * @Route("/{nalogID}/", name="")
     * @ParamConverter("nalog", options={"id" = "nalogID"})
     * @param Nalog $nalog
     * @param NalogNdsFetcher $fetcher
     * @return Response
     */
    public function index(Nalog $nalog, NalogNdsFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');

        $nalogsNds = $fetcher->all($nalog);

        return $this->render('app/finance/nalogNds/index.html.twig', [
            'nalogsNds' => $nalogsNds,
            'nalog' => $nalog,
        ]);
    }

    /**
     * @Route("/{nalogID}/create", name=".create")
     * @ParamConverter("nalog", options={"id" = "nalogID"})
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Nalog $nalog, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');

        $command = new Create\Command($nalog);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('nalog.nds', ['nalogID' => $nalog->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/nalogNds/create.html.twig', [
            'form' => $form->createView(),
            'nalog' => $nalog,
        ]);
    }

    /**
     * @Route("/{nalogID}/{id}/edit", name=".edit")
     * @ParamConverter("nalog", options={"id" = "nalogID"})
     * @param Nalog $nalog
     * @param NalogNds $nds
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Nalog $nalog, NalogNds $nds, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');

        $command = Edit\Command::fromNalogNds($nds);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('nalog.nds', ['nalogID' => $nalog->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/nalogNds/edit.html.twig', [
            'form' => $form->createView(),
            'nalog' => $nalog,
            'nds' => $nds
        ]);
    }

    /**
     * @Route("/{nalogID}/{id}/delete", name=".delete")
     * @ParamConverter("nalog", options={"id" = "nalogID"})
     * @param int $id
     * @param Request $request
     * @param NalogNdsRepository $nalogsNds
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Nalog $nalog, int $id, Request $request, NalogNdsRepository $nalogsNds, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $nds = $nalogsNds->get($id);
            if (!$nalogsNds->hasNdsMoreOne($nalog)) {
                return $this->json(['code' => 500, 'message' => "Невозможно удалить последнюю НДС"]);
            } else {
                $em->remove($nds);
                $flusher->flush();
                $data['message'] = 'НДС удалена';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

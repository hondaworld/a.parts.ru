<?php

namespace App\Controller\Finance;

use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Finance\Entity\Nalog\NalogRepository;
use App\Model\Finance\UseCase\Nalog\Edit;
use App\Model\Finance\UseCase\Nalog\Create;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\ReadModel\Finance\NalogFetcher;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/finance/nalog", name="nalog")
 */
class NalogController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param NalogFetcher $fetcher
     * @return Response
     */
    public function index(NalogFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');

        $nalogs = $fetcher->all();

        return $this->render('app/finance/nalog/index.html.twig', [
            'nalogs' => $nalogs,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('nalog');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/nalog/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Nalog $nalog
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Nalog $nalog, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');

        $command = Edit\Command::fromNalog($nalog);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('nalog');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/nalog/edit.html.twig', [
            'form' => $form->createView(),
            'nalog' => $nalog
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param NalogRepository $nalogs
     * @param FirmRepository $firmRepository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, Request $request, NalogRepository $nalogs, FirmRepository $firmRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Nalog');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $nalog = $nalogs->get($id);
            if ($firmRepository->hasByNalog($nalog) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить налоговую схему, прикрепленный к организациям']);
            } else {
                $em->remove($nalog);
                $flusher->flush();
                $data['message'] = 'Налоговая схема удалена';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}

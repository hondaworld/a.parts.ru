<?php

namespace App\Controller\Work;

use App\Model\Auto\Entity\Marka\AutoMarkaRepository;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Link\LinkWorkAutoRepository;
use App\Model\Work\Entity\Link\LinkWorkNormaAutoRepository;
use App\Model\Work\Entity\Link\LinkWorkPartsAutoRepository;
use App\Model\Work\UseCase\Group\Auto;
use App\ReadModel\Work\WorkGroupFetcher;
use App\Security\Voter\Work\WorkGroupVoter;
use App\Service\ManagerSettings;
use App\ReadModel\Work\Filter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/auto", name="work.auto")
 */
class WorkAutoController extends AbstractController
{
    /**
     * @Route("/{workGroupID}/", name="")
     * @param WorkGroup $workGroup
     * @param Request $request
     * @param AutoMarkaRepository $autoMarkaRepository
     * @param LinkWorkAutoRepository $linkWorkAutoRepository
     * @param LinkWorkNormaAutoRepository $linkWorkNormaAutoRepository
     * @param LinkWorkPartsAutoRepository $linkWorkPartsAutoRepository
     * @param WorkGroupFetcher $fetcher
     * @param ManagerSettings $settings
     * @param Auto\Handler $handler
     * @return Response
     */
    public function index(WorkGroup $workGroup, Request $request, AutoMarkaRepository $autoMarkaRepository, LinkWorkAutoRepository $linkWorkAutoRepository, LinkWorkNormaAutoRepository $linkWorkNormaAutoRepository, LinkWorkPartsAutoRepository $linkWorkPartsAutoRepository, WorkGroupFetcher $fetcher, ManagerSettings $settings, Auto\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(WorkGroupVoter::WORK_AUTO_CHANGE, 'WorkGroup');

        $filter = new Filter\WorkAuto\Filter();
        $formFilter = $this->createForm(Filter\WorkAuto\Form::class, $filter);
        $formFilter->handleRequest($request);

        if ($request->query->get('form') && $request->query->get('form')['auto_markaID']) {
            try {
                $autoMarkaID = $request->query->get('form')['auto_markaID'];
                $autoMarka = $autoMarkaRepository->get($autoMarkaID);

                $linkWorkAuto = $linkWorkAutoRepository->findByWorkGroup($workGroup);
                $linkWorkNormaAuto = $linkWorkNormaAutoRepository->findByWorkGroup($workGroup);
                $linkWorkPartsAuto = $linkWorkPartsAutoRepository->findByWorkGroup($workGroup);

                $command = Auto\Command::fromEntity($workGroup, $autoMarka, $linkWorkAuto, $linkWorkNormaAuto, $linkWorkPartsAuto);
                $form = $this->createForm(Auto\Form::class, $command);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $handler->handle($command);
                    return $this->redirectToRoute('work.auto', ['workGroupID' => $workGroup->getId(), 'form' => ['auto_markaID' => $autoMarkaID]]);
                }
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }


        }

        return $this->render('app/work/auto/index.html.twig', [
            'workGroup' => $workGroup,
            'autoMarka' => $autoMarka ?? null,
            'filter' => $formFilter->createView(),
            'form' => isset($form) ? $form->createView() : null
        ]);
    }
}

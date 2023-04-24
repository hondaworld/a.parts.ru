<?php


namespace App\Controller\Detail;


use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExcludeRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Detail\UseCase\ProviderExclude\Create;
use App\Model\Detail\UseCase\ProviderExclude\Edit;
use App\ReadModel\Detail\DetailProviderExcludeFetcher;
use App\ReadModel\Detail\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/exclude/providers", name="exclude.providers")
 */
class ExcludeProvidersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param DetailProviderExcludeFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, DetailProviderExcludeFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'DetailProviderExclude');

        $settings = $settings->get('providerExclude');

        $filter = new Filter\ProviderExclude\Filter();
        $filter->inPage = isset($settings['inPage']) ? $settings['inPage'] : $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ProviderExclude\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/detail/providerExclude/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'DetailProviderExclude');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('exclude.providers', ['page' => $request->getSession()->get('page/providerExclude') ?: 1]);

            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/providerExclude/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param DetailProviderExclude $detailProviderExclude
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(DetailProviderExclude $detailProviderExclude, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'DetailProviderExclude');
        $command = Edit\Command::fromEntity($detailProviderExclude);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('exclude.providers', ['page' => $request->getSession()->get('page/providerExclude') ?: 1]);

            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/providerExclude/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param DetailProviderExclude $detailProviderExclude
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(DetailProviderExclude $detailProviderExclude, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DetailProviderExclude');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($detailProviderExclude);
            $flusher->flush();
            $data['message'] = 'Закрытый регион удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param DetailProviderExcludeRepository $detailProviderExcludeRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, DetailProviderExcludeRepository $detailProviderExcludeRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DetailProviderExclude');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $detailProviderExclude = $detailProviderExcludeRepository->get($request->query->getInt('id'));
            $em->remove($detailProviderExclude);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
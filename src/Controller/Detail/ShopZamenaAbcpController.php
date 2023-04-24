<?php


namespace App\Controller\Detail;


use App\Model\Detail\Entity\ZamenaAbcp\ShopZamenaAbcp;
use App\Model\Detail\Entity\ZamenaAbcp\ShopZamenaAbcpRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Detail\ShopZamenaAbcpFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/zamenaAbcp", name="zamenaAbcp")
 */
class ShopZamenaAbcpController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ShopZamenaAbcpFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ShopZamenaAbcpFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopZamenaAbcp');

        $settings = $settings->get('shopZamenaAbcp');

        $filter = new Filter\ShopZamenaAbcp\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ShopZamenaAbcp\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/detail/zamenaAbcp/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ShopZamenaAbcp $shopZamenaAbcp
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ShopZamenaAbcp $shopZamenaAbcp, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopZamenaAbcp');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($shopZamenaAbcp);
            $flusher->flush();
            $data['message'] = 'Замена удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param ShopZamenaAbcpRepository $shopZamenaAbcpRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, ShopZamenaAbcpRepository $shopZamenaAbcpRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopZamenaAbcp');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $shopZamena = $shopZamenaAbcpRepository->get($request->query->getInt('id'));
            $em->remove($shopZamena);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
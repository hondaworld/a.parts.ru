<?php


namespace App\Controller\Detail;


use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\UseCase\Kit\CreateOrder;
use App\Model\Order\UseCase\Good\Create;
use App\Model\User\Entity\User\UserRepository;
use App\Model\Work\Entity\Period\WorkPeriod;
use App\Model\Work\Service\WorkPeriodService;
use App\ReadModel\Auto\AutoModelFetcher;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/parts-price/to", name="parts.price.to")
 */
class PartsPriceToController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param AutoModelFetcher $autoModelFetcher
     * @return Response
     */
    public function index(AutoModelFetcher $autoModelFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'WorkPeriod');

        $models = $autoModelFetcher->findWithTo();

        return $this->render('app/detail/to/index.html.twig', [
            'models' => $models,
        ]);
    }

    /**
     * @Route("/{id}/model", name=".model")
     * @param AutoModel $autoModel
     * @param AutoModelFetcher $autoModelFetcher
     * @return Response
     */
    public function model(AutoModel $autoModel, AutoModelFetcher $autoModelFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'WorkPeriod');

        $modifications = $autoModelFetcher->findModificationsWithTo($autoModel);

        return $this->render('app/detail/to/model.html.twig', [
            'autoModel' => $autoModel,
            'modifications' => $modifications
        ]);
    }

    /**
     * @Route("/{id}/modification", name=".modification")
     * @param AutoModification $autoModification
     * @return Response
     */
    public function modification(AutoModification $autoModification): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'WorkPeriod');

        return $this->render('app/detail/to/modification.html.twig', [
            'autoModification' => $autoModification
        ]);
    }

    /**
     * @Route("/{id}/to", name=".to")
     * @param WorkPeriod $workPeriod
     * @param WorkPeriodService $workPeriodService
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function to(WorkPeriod $workPeriod, WorkPeriodService $workPeriodService, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'WorkPeriod');

        $command = new CreateOrder\Command();
        $form = $this->createForm(CreateOrder\Form::class, $command);

        $groups = $workPeriodService->get($workPeriod);

        return $this->render('app/detail/to/to.html.twig', [
            'form' => $form->createView(),
            'workPeriod' => $workPeriod,
            'sklads' => $zapSkladFetcher->assoc(),
            'groups' => $groups
        ]);
    }
}
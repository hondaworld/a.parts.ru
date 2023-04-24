<?php

namespace App\Model\Firm\UseCase\ManagerFirm\Create;

use App\Form\Type\DatePickerType;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Firm\OrgGroupFetcher;
use App\ReadModel\Firm\OrgJobFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private OrgGroupFetcher $orgGroupFetcher;
    private OrgJobFetcher $orgJobFetcher;
    private ManagerFetcher $managerFetcher;
    private FirmFetcher $firmFetcher;

    public function __construct(OrgGroupFetcher $orgGroupFetcher, OrgJobFetcher $orgJobFetcher, ManagerFetcher $managerFetcher, FirmFetcher $firmFetcher)
    {

        $this->orgGroupFetcher = $orgGroupFetcher;
        $this->orgJobFetcher = $orgJobFetcher;
        $this->managerFetcher = $managerFetcher;
        $this->firmFetcher = $firmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('org_groupID', Type\ChoiceType::class, ['label' => 'Подразделение', 'choices' => array_flip($this->orgGroupFetcher->assoc()), 'placeholder' => ''])
            ->add('org_jobID', Type\ChoiceType::class, ['label' => 'Должность', 'choices' => array_flip($this->orgJobFetcher->assoc()), 'placeholder' => ''])
            ->add('dateofadded', DatePickerType::class, ['required' => false, 'label' => 'Дата принятия'])
            ->add('dateofclosed', DatePickerType::class, ['required' => false, 'label' => 'Дата увольнения'])
        ;

        if ($options['data']->firm) {
            $builder->add('managerID', Type\ChoiceType::class, ['label' => 'Сотрудник', 'choices' => array_flip($this->managerFetcher->assocByFirm($options['data']->firm)), 'placeholder' => '']);
        }

        if ($options['data']->manager) {
            $builder->add('firmID', Type\ChoiceType::class, ['label' => 'Организация', 'choices' => array_flip($this->firmFetcher->assocByManager($options['data']->manager)), 'placeholder' => '']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

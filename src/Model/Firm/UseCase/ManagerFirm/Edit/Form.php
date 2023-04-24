<?php

namespace App\Model\Firm\UseCase\ManagerFirm\Edit;

use App\Form\Type\DatePickerType;
use App\ReadModel\Firm\OrgGroupFetcher;
use App\ReadModel\Firm\OrgJobFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private OrgGroupFetcher $orgGroupFetcher;
    private OrgJobFetcher $orgJobFetcher;

    public function __construct(OrgGroupFetcher $orgGroupFetcher, OrgJobFetcher $orgJobFetcher)
    {

        $this->orgGroupFetcher = $orgGroupFetcher;
        $this->orgJobFetcher = $orgJobFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('org_groupID', Type\ChoiceType::class, ['label' => 'Подразделение', 'choices' => array_flip($this->orgGroupFetcher->assoc()), 'placeholder' => ''])
            ->add('org_jobID', Type\ChoiceType::class, ['label' => 'Должность', 'choices' => array_flip($this->orgJobFetcher->assoc()), 'placeholder' => ''])
            ->add('dateofadded', DatePickerType::class, ['required' => false, 'label' => 'Дата принятия'])
            ->add('dateofclosed', DatePickerType::class, ['required' => false, 'label' => 'Дата увольнения'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

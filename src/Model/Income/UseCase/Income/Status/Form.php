<?php

namespace App\Model\Income\UseCase\Income\Status;


use App\Form\Type\DatePickerType;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Shop\DeleteReasonFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $incomeStatusFetcher;
    private $deleteReasonFetcher;

    public function __construct(IncomeStatusFetcher $incomeStatusFetcher, DeleteReasonFetcher $deleteReasonFetcher)
    {
        $this->incomeStatusFetcher = $incomeStatusFetcher;
        $this->deleteReasonFetcher = $deleteReasonFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Статус',
                'choices' => array_flip($this->incomeStatusFetcher->assocAllowChange()),
                'placeholder' => ''
            ])
            ->add('deleteReasonID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Причина отказа',
                'choices' => array_flip($this->deleteReasonFetcher->assoc()),
                'placeholder' => ''
            ])
            ->add('dateofinplan', DatePickerType::class, ['label' => 'Планируемая дата прихода']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}

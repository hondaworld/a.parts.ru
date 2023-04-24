<?php


namespace App\ReadModel\Reports\Filter\RegionProfit;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateofreport', DateIntervalPickerType::class, [])
            ->add('dateofprev', DatePickerType::class, ['filter' => true, 'label' => 'Дата сравнения'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
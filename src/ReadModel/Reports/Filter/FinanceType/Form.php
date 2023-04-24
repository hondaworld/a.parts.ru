<?php


namespace App\ReadModel\Reports\Filter\FinanceType;


use App\Form\Type\DateIntervalPickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateofreport', DateIntervalPickerType::class, [])
            ->add('period', Type\ChoiceType::class, ['filter' => true, 'choices' => [
                'По годам' => 'year',
                'По месяцам' => 'month',
                'По дням' => 'day'
            ],
                'attr' => [
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => false]);
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
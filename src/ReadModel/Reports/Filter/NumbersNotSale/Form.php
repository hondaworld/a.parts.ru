<?php


namespace App\ReadModel\Reports\Filter\NumbersNotSale;


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
            ->add('days', Type\ChoiceType::class, ['filter' => true, 'choices' => [
                '1 месяц' => '30',
                '3 месяца' => '90',
                '6 месяцев' => '180',
                'Никогда' => 'all'
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
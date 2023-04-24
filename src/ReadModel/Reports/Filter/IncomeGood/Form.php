<?php


namespace App\ReadModel\Reports\Filter\IncomeGood;


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
            ->add('sklad', Type\ChoiceType::class, ['filter' => true, 'choices' => [
                'Складские' => 'sklad',
                'Заказные' => 'zakaz'
            ],
                'attr' => [
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => 'Все']);
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
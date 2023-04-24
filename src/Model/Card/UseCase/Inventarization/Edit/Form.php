<?php

namespace App\Model\Card\UseCase\Inventarization\Edit;


use App\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateofadded', DatePickerType::class, ['label' => 'Дата'])
            ->add('isClose', Type\CheckboxType::class, ['required' => false, 'label' => 'Закрыть', 'type' => 'danger', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

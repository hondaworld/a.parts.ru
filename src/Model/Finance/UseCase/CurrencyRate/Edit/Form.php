<?php

namespace App\Model\Finance\UseCase\CurrencyRate\Edit;


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
            ->add('rate', Type\TextType::class, ['label' => 'Курс 1 руб.', 'attr' => ['class' => 'js-convert-float']])
            ->add('dateofadded', DatePickerType::class, ['label' => 'Дата'])
            ->add('numbers', Type\TextType::class, ['label' => 'Единиц', 'attr' => ['class' => 'js-convert-number']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

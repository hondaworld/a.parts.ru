<?php

namespace App\Model\Firm\UseCase\Firm\Others;

use App\Form\Type\DatePickerType;
use App\ReadModel\Finance\NalogFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_schet', Type\TextType::class, ['label' => 'Счет', 'attr' => ['class' => 'js-convert-number']])
            ->add('first_nakladnaya', Type\TextType::class, ['label' => 'Накладная', 'attr' => ['class' => 'js-convert-number']])
            ->add('first_schetfak', Type\TextType::class, ['label' => 'Счет/фактура', 'attr' => ['class' => 'js-convert-number']])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

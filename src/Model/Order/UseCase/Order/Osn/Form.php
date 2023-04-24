<?php

namespace App\Model\Order\UseCase\Order\Osn;

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
            ->add('name', Type\TextType::class, ['required' => false, 'label' => 'Основание'])
            ->add('number', Type\TextType::class, ['required' => false, 'label' => 'Номер документа', 'attr' => ['maxLength' => 50]])
            ->add('dateofadded', DatePickerType::class, ['required' => false, 'label' => 'Дата документа'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

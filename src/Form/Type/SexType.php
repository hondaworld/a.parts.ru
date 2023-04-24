<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SexType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Пол',
            'choices' => ['Мужской' => 'M', 'Женский' => 'F'],
            'expanded' => true,
            'multiple' => false,
            'label_attr' => ['class' => 'radio-custom radio-inline'],
            'placeholder' => 'Не задано',
        ]);
    }

    public function getParent(): string
    {
        return Type\ChoiceType::class;
    }
}
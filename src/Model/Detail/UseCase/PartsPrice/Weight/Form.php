<?php

namespace App\Model\Detail\UseCase\PartsPrice\Weight;


use App\Form\Type\FloatNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', Type\HiddenType::class)
            ->add('createrID', Type\HiddenType::class)
            ->add('weight', FloatNumberType::class, ['label' => 'Вес в кг'])
            ->add('weightIsReal', Type\CheckboxType::class, ['required' => false, 'type' => 'success', 'label' => 'Проверен', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

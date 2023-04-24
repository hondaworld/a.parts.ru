<?php

namespace App\Model\Detail\UseCase\PartsPrice\Price;

use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Detail\CreaterFetcher;
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
            ->add('providerPriceID', Type\HiddenType::class)
            ->add('price', FloatNumberType::class, ['label' => 'Цена'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Card\UseCase\Card\Profit;


use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price1', FloatNumberType::class, ['required' => false, 'label' => 'Цена реализации'])
            ->add('profit', IntegerNumberType::class, ['required' => false, 'label' => 'Наценка, %'])
//            ->add('profit', Type\TextType::class, ['required' => false, 'label' => 'Наценка, %'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Card\UseCase\StockNumber\Price;

use App\Form\Type\FloatNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['data']->stockNumbers as $numberID => $price) {
            $builder->add($options['data']->getPrice($numberID), FloatNumberType::class, ['required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Card\UseCase\Card\ProfitZapCard;

use App\Form\Type\FloatNumberNegativeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['data']->opts as $opt) {
            $builder->add($options['data']->getProfit($opt->getId()), FloatNumberNegativeType::class, ['required' => false]);
        }
        $builder->add($options['data']->getProfit(0), FloatNumberNegativeType::class, ['required' => false]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Sklad\UseCase\PriceList\Opt;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['data']->opts as $optID => $opt) {
            $builder->add($options['data']->getProfit($optID), Type\TextType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm js-convert-float']]);
        }
        $builder->add($options['data']->getProfit(0), Type\TextType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm js-convert-float']]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

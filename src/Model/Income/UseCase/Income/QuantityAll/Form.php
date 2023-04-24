<?php

namespace App\Model\Income\UseCase\Income\QuantityAll;

use App\Form\Type\IntegerNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('quantity', IntegerNumberType::class, ['required' => false]);
        $builder->add('quantityIn', IntegerNumberType::class, ['required' => false]);
        $builder->add('quantityPath', IntegerNumberType::class, ['required' => false]);
        $builder->add('reserve', IntegerNumberType::class, ['required' => false]);
        $builder->add('quantityReturn', IntegerNumberType::class, ['required' => false]);
        foreach (array_keys($options['data']->incomeSklads) as $zapSkladID) {
            $builder->add($options['data']->getQuantity($zapSkladID), IntegerNumberType::class, ['required' => false]);
            $builder->add($options['data']->getQuantityIn($zapSkladID), IntegerNumberType::class, ['required' => false]);
            $builder->add($options['data']->getQuantityPath($zapSkladID), IntegerNumberType::class, ['required' => false]);
            $builder->add($options['data']->getReserve($zapSkladID), IntegerNumberType::class, ['required' => false]);
            $builder->add($options['data']->getQuantityReturn($zapSkladID), IntegerNumberType::class, ['required' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

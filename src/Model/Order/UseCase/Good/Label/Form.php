<?php

namespace App\Model\Order\UseCase\Good\Label;

use App\Form\Type\IntegerNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (array_keys($options['data']->goods) as $goodID) {
            $builder->add('quantity_' . $goodID, IntegerNumberType::class, ['required' => false]);
            $builder->add('isCheck_' . $goodID, Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => false, 'label_attr' => ['class' => 'checkbox-custom']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Income\UseCase\Income\Label;

use App\Form\Type\IntegerNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (array_keys($options['data']->incomes) as $incomeID) {
            $builder->add('quantity_' . $incomeID, IntegerNumberType::class, ['required' => false]);
            $builder->add('isCheck_' . $incomeID, Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => false, 'label_attr' => ['class' => 'checkbox-custom']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

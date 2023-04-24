<?php

namespace App\Model\Provider\UseCase\Price\Num;

use App\Model\Provider\Entity\Price\Num;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('maxCols', Type\HiddenType::class)
            ->add('price', Type\HiddenType::class)
            ->add('isCols', Type\HiddenType::class);
        foreach ($options['data']->fields as $k => $field) {
            $builder->add($options['data']->getField($k), Type\ChoiceType::class, ['choices' => array_flip(Num::assoc()), 'placeholder' => '', 'attr' => ['style' => 'min-width: 70px;', 'class' => 'form-control-sm']]);
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Order\UseCase\Good\Blank;


use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isHideNumbers', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Скрыть номера'])
            ->add('isShowSrok', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Показать срок'])
            ->add('cols', Type\HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

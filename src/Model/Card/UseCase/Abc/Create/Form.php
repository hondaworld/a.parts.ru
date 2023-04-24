<?php

namespace App\Model\Card\UseCase\Abc\Create;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('abc', Type\TextType::class, ['label' => 'ABC', 'attr' => ['maxLength' => 2]])
            ->add('description', Type\TextType::class, ['required' => false, 'label' => 'Описание'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

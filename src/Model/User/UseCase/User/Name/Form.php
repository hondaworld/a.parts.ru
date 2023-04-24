<?php

namespace App\Model\User\UseCase\User\Name;

use App\Form\Type\AutocompleteType;
use App\Model\User\UseCase\User\Town;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['required' => false, 'label' => 'Отображаемое имя', 'help' => 'Оставьте пустым для перегенерации'])
            ->add('firstname', Type\TextType::class, ['label' => 'Имя', 'attr' => ['class' => 'js-convert-name']])
            ->add('lastname', Type\TextType::class, ['required' => false, 'label' => 'Фамилия', 'attr' => ['class' => 'js-convert-name']])
            ->add('middlename', Type\TextType::class, ['required' => false, 'label' => 'Отчество', 'attr' => ['class' => 'js-convert-name']])
            ->add('town', AutocompleteType::class, ['required' => false, 'label' => 'Город', 'url' => '/api/towns', 'data_class' => Town::class]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

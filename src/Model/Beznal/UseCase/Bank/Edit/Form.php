<?php

namespace App\Model\Beznal\UseCase\Bank\Edit;

use App\Form\Type\AutocompleteType;
use App\Model\Beznal\UseCase\Beznal\Bank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bik', Type\TextType::class, ['label' => 'БИК', 'attr' => ['maxLength' => 15]])
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('korschet', Type\TextType::class, ['label' => 'Кор. счет'])
            ->add('address', Type\TextareaType::class, ['label' => 'Адрес'])
            ->add('description', Type\TextareaType::class, ['label' => 'Примечание', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Beznal\UseCase\Beznal\Edit;

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
            ->add('bank', AutocompleteType::class, ['label' => 'Банк', 'url' => '/api/banks', 'data_class' => Bank::class])
            ->add('rasschet', Type\TextType::class, ['label' => 'Рассчетный счет'])
            ->add('description', Type\TextareaType::class, ['label' => false, 'required' => false])
            ->add('isMain', Type\CheckboxType::class, ['required' => false, 'label' => 'Основной реквизит', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

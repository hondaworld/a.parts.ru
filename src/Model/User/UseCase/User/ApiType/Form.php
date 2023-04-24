<?php

namespace App\Model\User\UseCase\User\ApiType;

use App\Form\Type\AutocompleteType;
use App\Model\User\Entity\User\User;
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
            ->add('apiType', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Api для клиента',
                'choices' => array_flip(User::API_TYPES),
                'placeholder' => false
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

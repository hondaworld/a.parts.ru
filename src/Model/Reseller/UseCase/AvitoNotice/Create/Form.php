<?php

namespace App\Model\Reseller\UseCase\AvitoNotice\Create;

use App\Form\Type\AutocompleteSimpleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', AutocompleteSimpleType::class, ['label' => 'Номер детали из номенклатуры', 'url' => '/api/zapCardNumbers'])
            ->add('zapCardID', Type\HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

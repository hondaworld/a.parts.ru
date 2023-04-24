<?php

namespace App\Model\Order\UseCase\Order\Create;

use App\Form\Type\AutocompleteType;
use App\Form\Type\PhoneMobileType;
use App\Model\User\UseCase\User\Phonemob;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\User\OptFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phonemob', Type\HiddenType::class)
            ->add('firstname', Type\TextType::class, ['label' => 'Имя', 'attr' => ['class' => 'js-convert-name']])
            ->add('lastname', Type\TextType::class, ['required' => false, 'label' => 'Фамилия', 'attr' => ['class' => 'js-convert-name']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

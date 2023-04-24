<?php

namespace App\Model\User\UseCase\User\Create;

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
    private OptFetcher $optFetcher;

    public function __construct(OptFetcher $optFetcher)
    {

        $this->optFetcher = $optFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phonemob', PhoneMobileType::class, ['label' => 'Мобильный телефон', 'data_class' => Phonemob::class])
            ->add('firstname', Type\TextType::class, ['label' => 'Имя', 'attr' => ['class' => 'js-convert-name']])
            ->add('lastname', Type\TextType::class, ['required' => false, 'label' => 'Фамилия', 'attr' => ['class' => 'js-convert-name']])
            ->add('middlename', Type\TextType::class, ['required' => false, 'label' => 'Отчество', 'attr' => ['class' => 'js-convert-name']])
            ->add('town', AutocompleteType::class, ['required' => false, 'label' => 'Город', 'url' => '/api/towns', 'data_class' => Town::class])
            ->add('optID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Оптовый пакет',
                'choices' => array_flip($this->optFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\User\UseCase\User\Ur;

use App\Form\Type\AutocompleteType;
use App\Form\Type\DatePickerType;
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
            ->add('organization', Type\TextType::class, ['required' => false, 'label' => 'Полное наименование организации'])
            ->add('inn', Type\TextType::class, ['required' => false, 'label' => 'ИНН', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 12]])
            ->add('kpp', Type\TextType::class, ['required' => false, 'label' => 'КПП', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 9]])
            ->add('okpo', Type\TextType::class, ['required' => false, 'label' => 'ОКПО', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 8]])
            ->add('ogrn', Type\TextType::class, ['required' => false, 'label' => 'ОГРН', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 15]])
            ->add('dogovor_num', Type\TextType::class, ['required' => false, 'label' => 'Номер договора', 'attr' => ['maxLength' => 50]])
            ->add('dogovor_date', DatePickerType::class, ['required' => false, 'label' => 'Дата договора'])
            ->add('isNDS', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Является плательщиком НДС', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isUr', Type\CheckboxType::class, ['required' => false, 'type' => 'danger', 'label' => 'Является юридическим лицом', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

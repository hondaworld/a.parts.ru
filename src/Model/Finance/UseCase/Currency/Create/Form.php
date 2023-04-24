<?php

namespace App\Model\Finance\UseCase\Currency\Create;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', Type\TextType::class, ['label' => 'Код', 'attr' => ['class' => 'js-convert-number']])
            ->add('name_short', Type\TextType::class, ['label' => 'Краткое наименование', 'attr' => ['maxLength' => 3]])
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('koef', Type\TextType::class, ['label' => 'Коэффициент', 'attr' => ['class' => 'js-convert-float']])
            ->add('sex', Type\ChoiceType::class, [
                'label' => 'Род',
                'choices' => [
                    'Средний' => 'A',
                    'Мужской' => 'M',
                    'Женский' => 'F',
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('fix_rate', Type\TextType::class, ['required' => false, 'label' => 'Фиксированный курс', 'attr' => ['class' => 'js-convert-float']])
            ->add('is_fix_rate', Type\CheckboxType::class, ['required' => false, 'label' => 'Использовать фиксированный курс', 'label_attr' => ['class' => 'switch-custom']])
            ->add('int_name', Type\TextType::class, ['label' => 'Сокращение'])
            ->add('int_1', Type\TextType::class, ['label' => 'Один (а)'])
            ->add('int_2', Type\TextType::class, ['label' => 'Два (е)'])
            ->add('int_5', Type\TextType::class, ['label' => 'Пять'])
            ->add('fract_name', Type\TextType::class, ['label' => 'Сокращение'])
            ->add('fract_1', Type\TextType::class, ['label' => 'Один (а)'])
            ->add('fract_2', Type\TextType::class, ['label' => 'Два (е)'])
            ->add('fract_5', Type\TextType::class, ['label' => 'Пять']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

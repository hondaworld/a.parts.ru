<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PhoneMobileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('countryPhone', Type\ChoiceType::class, [
                'choices' => [
                    'Россия +7' => '+7(999) 999-99-99',
                    'Украина +380' => '+380(999) 999-99-99',
                    'Белоруссия +375' => '+375(99) 999-99-99',
                    'Казахстан +77' => '7(799) 999-99-99',
                    ],
                'attr' => ['class' => 'country-phone w-auto custom-select'],
                'label' => false
            ])
            ->add('phonemob', Type\TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => ['class' => 'js-masked-phonemob'],
        ]);
    }

    public function getParent(): string
    {
        return Type\FormType::class;
    }
}
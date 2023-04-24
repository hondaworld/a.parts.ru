<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

class DateIntervalPickerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_from', Type\TextType::class, [
                'required' => false,
                'attr' => [
                    'data-week-start' => '1',
                    'data-autoclose' => 'true',
                    'data-today-highlight' => 'true',
                    'class' => 'form-control-sm',
                    'placeholder' => 'Дата с'
                ],
            ])
            ->add('date_till', Type\TextType::class, [
                'required' => false,
                'attr' => [
                    'data-week-start' => '1',
                    'data-autoclose' => 'true',
                    'data-today-highlight' => 'true',
                    'class' => 'form-control-sm',
                    'placeholder' => 'Дата по'
                ],
            ]);
    }

    public function getParent(): string
    {
        return Type\FormType::class;
    }

}
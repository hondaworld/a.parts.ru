<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => 'H',
            'with_minutes' => true,
            'html5' => false,
            'attr' => [
                'class' => 'js-flatpickr bg-white w-auto',
                'data-enable-time' => 'true',
                'data-no-calendar' => 'true',
                'data-date-format' => 'H:i',
                'data-time_24hr' => 'true',
            ],
        ]);
    }

    public function getParent(): string
    {
        return Type\TimeType::class;
    }
}
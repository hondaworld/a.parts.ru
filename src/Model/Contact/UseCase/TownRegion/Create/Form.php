<?php

namespace App\Model\Contact\UseCase\TownRegion\Create;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование', 'attr' => ['maxLength' => 50]])
            ->add('daysFromMoscow', Type\TextType::class, ['label' => 'Дней до Москвы', 'attr' => ['class' => 'js-convert-number']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Card\UseCase\Measure\Edit;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('name_short', Type\TextType::class, ['label' => 'Наименование краткое', 'attr' => ['maxLength' => 10]])
            ->add('okei', Type\TextType::class, ['label' => 'Код ОКЕИ', 'attr' => ['maxLength' => 5]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

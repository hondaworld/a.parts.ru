<?php

namespace App\Model\Manager\UseCase\NewsAdmin\Create;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
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
            ->add('description', CKEditorType::class, ['required' => false, 'label' => 'Текст'])
            ->add('type', Type\ChoiceType::class, [
                'label' => 'Тип',
                'choices' => [
                    'Объявление' => 1,
                    'Новость' => 2,
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

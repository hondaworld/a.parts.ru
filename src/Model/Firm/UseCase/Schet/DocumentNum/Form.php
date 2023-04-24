<?php

namespace App\Model\Firm\UseCase\Schet\DocumentNum;


use App\Form\Type\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('document_prefix', Type\TextType::class, ['required' => false, 'label' => 'Префикс', 'attr' => ['maxLength' => 15]])
            ->add('document_sufix', Type\TextType::class, ['required' => false, 'label' => 'Суфикс', 'attr' => ['maxLength' => 15]])
            ->add('comment', Type\TextareaType::class, ['required' => false, 'label' => 'Комментарий'])
            ->add('dateofadded', DatePickerType::class, ['label' => 'Дата добавления'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

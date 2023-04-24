<?php

namespace App\Model\User\UseCase\User\Review;

use App\Form\Type\AutocompleteType;
use App\Model\User\Entity\User\User;
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
            ->add('reviewUrl', Type\TextType::class, ['required' => false, 'label' => 'Адрес с отзывом'])
            ->add('isReviewSend', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Уведомление отправлено', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isReview', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Отзыв размещен', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

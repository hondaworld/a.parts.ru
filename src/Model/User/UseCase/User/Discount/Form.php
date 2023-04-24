<?php

namespace App\Model\User\UseCase\User\Discount;

use App\Form\Type\AutocompleteType;
use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
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
            ->add('schetDays', IntegerNumberType::class, ['required' => false, 'label' => 'Срок оплаты (дней)'])
            ->add('discountParts', FloatNumberType::class, ['required' => false, 'label' => 'Скидка в магазине'])
            ->add('discountService', FloatNumberType::class, ['required' => false, 'label' => 'Скидка в сервисе'])
            ->add('is_not_update_discount', Type\CheckboxType::class, ['required' => false, 'type' => 'danger', 'label' => 'Запретить менять скидку при формировании накладных', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

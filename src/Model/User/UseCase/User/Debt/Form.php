<?php

namespace App\Model\User\UseCase\User\Debt;

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
            ->add('balanceLimit', FloatNumberType::class, ['required' => false, 'label' => 'Максимальная задолженность'])
            ->add('debts_days', IntegerNumberType::class, ['required' => false, 'label' => 'Максимальное количество дней'])
            ->add('debtInDays', FloatNumberType::class, ['required' => false, 'label' => 'Количество дней по договору'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

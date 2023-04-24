<?php

namespace App\Model\Firm\UseCase\BalanceHistory\Edit;

use App\Form\Type\DatePickerType;
use App\Form\Type\FloatNumberNegativeType;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('dateofadded', DatePickerType::class, [
//                'required' => true,
//                'label' => 'Дата'
//            ])
            ->add('balance', FloatNumberNegativeType::class, ['required' => true, 'label' => 'Сумма'])
            ->add('balance_nds', FloatNumberNegativeType::class, ['required' => false, 'label' => 'НДС'])
            ->add('description', Type\TextareaType::class, ['required' => false, 'label' => 'Комментарий'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

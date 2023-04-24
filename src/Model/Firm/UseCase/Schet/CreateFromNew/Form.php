<?php

namespace App\Model\Firm\UseCase\Schet\CreateFromNew;


use App\Form\Type\FloatNumberType;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\ReadModel\Firm\FirmFetcher;
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
            ->add('document_prefix', Type\TextType::class, ['required' => false, 'label' => 'Префикс', 'attr' => ['maxLength' => 15]])
            ->add('document_sufix', Type\TextType::class, ['required' => false, 'label' => 'Суфикс', 'attr' => ['maxLength' => 15]])
            ->add('finance_typeID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Тип счета',
                'choices' => [
                    'Обычный счет' => FinanceType::DEFAULT_BEZNAL_ID,
                    'Оплата картой' => FinanceType::DEFAULT_BEZNAL_CARD_ID
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false
            ])
            ->add('isEmail', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Отправить уведомление', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

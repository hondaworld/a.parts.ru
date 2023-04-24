<?php

namespace App\Model\Sklad\UseCase\PriceList\Create;

use App\Form\Type\FloatNumberType;
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
            ->add('koef_dealer', FloatNumberType::class, ['required' => false, 'label' => 'Процент от дилерской цены (если цена больше)', 'help' => 'Задайте "0", если не учитывать'])
            ->add('no_discount', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Не учитывать скидку', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isMain', Type\CheckboxType::class, ['required' => false, 'label' => 'Основной прайс-лист', 'label_attr' => ['class' => 'switch-custom']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

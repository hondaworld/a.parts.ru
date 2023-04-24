<?php

namespace App\Model\Contact\UseCase\Contact\Create;

use App\Form\Type\AddressType;
use App\Form\Type\PhoneMobileType;
use App\Model\Contact\UseCase\Contact\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', AddressType::class, ['label' => false, 'data_class' => Address::class])
            ->add('phonemob', PhoneMobileType::class, ['label' => 'Мобильный телефон', 'required' => false])
            ->add('phone', Type\TextType::class, ['label' => 'Телефон', 'required' => false])
            ->add('fax', Type\TextType::class, ['label' => 'Факс', 'required' => false])
            ->add('http', Type\TextType::class, ['label' => 'Домашняя страница', 'required' => false])
            ->add('email', Type\TextType::class, ['label' => 'E-mail', 'required' => false])
            ->add('description', Type\TextareaType::class, ['label' => false, 'required' => false])
            ->add('isUr', Type\CheckboxType::class, ['required' => false, 'label' => 'Является юридическим лицом', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isMain', Type\CheckboxType::class, ['required' => false, 'label' => 'Основной контакт', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

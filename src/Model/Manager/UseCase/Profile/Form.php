<?php

namespace App\Model\Manager\UseCase\Profile;

use App\Form\Type\AutocompleteType;
use App\Form\Type\DatePickerType;
use App\Form\Type\ImageType;
use App\Form\Type\PhoneMobileType;
use App\Form\Type\SexType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', Type\TextType::class, ['label' => 'Логин'])
            ->add('name', Type\TextType::class, ['label' => 'Отображаемое имя'])
            ->add('firstname', Type\TextType::class, ['label' => 'Имя'])
            ->add('lastname', Type\TextType::class, ['label' => 'Фамилия'])
            ->add('middlename', Type\TextType::class, ['required' => false, 'label' => 'Отчество'])
            ->add('phonemob', PhoneMobileType::class, ['label' => 'Мобильный телефон', 'data_class' => Phonemob::class])
            ->add('email', Type\EmailType::class, ['required' => false, 'label' => 'E-mail'])
//            ->add('town', AutocompleteType::class, ['label' => 'Город', 'url' => '/api/towns', 'data_class' => Town::class])
            ->add('dateofmanager', DatePickerType::class, ['required' => false, 'label' => 'Дата рождения'])
            ->add('sex', SexType::class)
//            ->add('description', CKEditorType::class)
            ->add('photo', ImageType::class, [
                'label' => 'Фотография',
                'delete_url' => 'profile.photo.delete',
                'delete_message' => 'Вы уверены, что хотите удалить фотографию?',
                'is_vertical' => false
            ]);
//            ->add('isHide', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Заблокировать', 'label_attr' => ['class' => 'checkbox-custom']])
//            ->add('isAdmin', Type\CheckboxType::class, ['required' => false, 'type' => 'danger', 'label' => 'Супер администратор', 'label_attr' => ['class' => 'switch-custom']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

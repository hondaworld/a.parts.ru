<?php

namespace App\Model\Manager\UseCase\Manager\Edit;

use App\Form\Type\DatePickerType;
use App\Form\Type\ImageType;
use App\Form\Type\PhoneMobileType;
use App\Form\Type\SexType;
use App\ReadModel\Manager\ManagerGroupFetcher;
use App\ReadModel\Manager\ManagerTypeFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Form extends AbstractType
{
    private ManagerGroupFetcher $groups;
    private ZapSkladFetcher $sklads;
    private ManagerTypeFetcher $types;
    private AuthorizationCheckerInterface $auth;

    public function __construct(ManagerGroupFetcher $groups, ZapSkladFetcher $sklads, ManagerTypeFetcher $types, AuthorizationCheckerInterface $auth)
    {
        $this->groups = $groups;
        $this->sklads = $sklads;
        $this->types = $types;
        $this->auth = $auth;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', Type\TextType::class, ['label' => 'Логин'])
            ->add('password', Type\PasswordType::class, ['required' => false, 'label' => 'Пароль', 'is_generate' => true, 'help' => 'Не заполняйте, если хотите оставить прежний пароль'])
            ->add('name', Type\TextType::class, ['label' => 'Отображаемое имя'])
            ->add('nick', Type\TextType::class, ['label' => 'Ник', 'attr' => ['maxLength' => 3]])
            ->add('firstname', Type\TextType::class, ['label' => 'Имя'])
            ->add('lastname', Type\TextType::class, ['label' => 'Фамилия'])
            ->add('middlename', Type\TextType::class, ['required' => false, 'label' => 'Отчество'])
            ->add('zp_spare', Type\TextType::class, ['required' => false, 'label' => 'Магазин: % З.П.', 'attr' => ['class' => 'js-convert-float']])
            ->add('zp_service', Type\TextType::class, ['required' => false, 'label' => 'Сервис: % З.П.', 'attr' => ['class' => 'js-convert-float']])
            ->add('phonemob', PhoneMobileType::class, ['required' => false, 'label' => 'Мобильный телефон', 'data_class' => Phonemob::class])
            ->add('email', Type\EmailType::class, ['required' => false, 'label' => 'E-mail'])
            ->add('dateofmanager', DatePickerType::class, ['required' => false, 'label' => 'Дата рождения'])
            ->add('sex', SexType::class, ['required' => false])
            ->add('photo', ImageType::class, [
                'label' => 'Фотография',
                'delete_url' => 'managers.photo.delete',
                'delete_params' => ['id' => $options['data']->managerID],
                'delete_message' => 'Вы уверены, что хотите удалить фотографию?',
                'is_vertical' => false
            ])
            ->add('groups', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Группы прав',
                'choices' => array_flip($this->groups->assoc()),
                'expanded' => false,
                'multiple' => true,
                'attr' => ['class' => 'js-select2', 'size' => 1]
            ])
            ->add('sklads', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Склады, которым менеджер имеет отношение',
                'choices' => array_flip($this->sklads->assoc()),
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline']
            ])
            ->add('managerTypeID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Тип',
                'choices' => array_flip($this->types->assoc()),
                'expanded' => false,
                'multiple' => false
            ])
            ->add('isHide', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Заблокировать', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isManager', Type\CheckboxType::class, ['required' => false, 'type' => 'success', 'label' => 'Менеджер', 'label_attr' => ['class' => 'switch-custom']]);

        if ($options['attr']['isAdmin'] || $this->auth->isGranted('ROLE_SUPER_ADMIN')) $builder->add('isAdmin', Type\CheckboxType::class, ['required' => false, 'type' => 'danger', 'label' => 'Супер администратор', 'label_attr' => ['class' => 'switch-custom']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

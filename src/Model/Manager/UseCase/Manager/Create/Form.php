<?php

namespace App\Model\Manager\UseCase\Manager\Create;

use App\ReadModel\Manager\ManagerGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    /**
     * @var ManagerGroupFetcher
     */
    private ManagerGroupFetcher $groups;

    public function __construct(ManagerGroupFetcher $groups) {
        $this->groups = $groups;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', Type\TextType::class, ['label' => 'Логин', 'attr' => ['class' => 'js-convert-login']])
            ->add('password', Type\PasswordType::class, ['label' => 'Пароль', 'is_generate' => true])
            ->add('firstname', Type\TextType::class, ['label' => 'Имя', 'attr' => ['class' => 'js-convert-name']])
            ->add('lastname', Type\TextType::class, ['label' => 'Фамилия', 'attr' => ['class' => 'js-convert-name']])
            ->add('middlename', Type\TextType::class, ['required' => false, 'label' => 'Отчество', 'attr' => ['class' => 'js-convert-name']])
            ->add('groups', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Группы прав',
                'choices' => array_flip($this->groups->assoc()),
                'expanded' => false,
                'multiple' => true,
                'attr' => ['class' => 'js-select2', 'size' => 1]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

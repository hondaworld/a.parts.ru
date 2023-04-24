<?php

namespace App\Model\User\UseCase\User\Email;

use App\Form\Type\AutocompleteType;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\User\UserEmailStatusFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $emailStatusFetcher;

    public function __construct(UserEmailStatusFetcher $emailStatusFetcher)
    {
        $this->emailStatusFetcher = $emailStatusFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', Type\EmailType::class, ['required' => false, 'label' => 'E-mail'])
            ->add('isActive', Type\CheckboxType::class, ['required' => false, 'type' => 'danger', 'label' => 'Подтвержден', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isNotification', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Разрешить e-mail рассылку', 'label_attr' => ['class' => 'switch-custom']])
            ->add('excludeEmailStatuses', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Разрешить E-mail рассылку',
                'choices' => array_flip($this->emailStatusFetcher->assoc()),
                'expanded' => true,
                'multiple' => true,
                'label_attr' => ['class' => 'checkbox-custom']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

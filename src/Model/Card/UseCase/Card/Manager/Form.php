<?php

namespace App\Model\Card\UseCase\Card\Manager;


use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ManagerFetcher $managerFetcher;

    public function __construct(ManagerFetcher $managerFetcher)
    {
        $this->managerFetcher = $managerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('managerID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Менеджер',
                'choices' => array_flip($this->managerFetcher->assoc(true)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

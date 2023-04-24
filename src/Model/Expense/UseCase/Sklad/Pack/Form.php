<?php

namespace App\Model\Expense\UseCase\Sklad\Pack;


use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $managerFetcher;

    public function __construct(ManagerFetcher $managerFetcher)
    {
        $this->managerFetcher = $managerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isDelete', Type\HiddenType::class)
            ->add('managerID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Сборщик',
                'choices' => array_flip($this->managerFetcher->assoc(true)),
                'placeholder' => ''
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}

<?php

namespace App\Model\User\UseCase\User\Manager;

use App\Form\Type\AutocompleteType;
use App\Form\Type\PhoneMobileType;
use App\Model\User\UseCase\User\Phonemob;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\User\OptFetcher;
use App\ReadModel\User\ShopPayTypeFetcher;
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
                'label' => 'Менеджер',
                'choices' => array_flip($this->managerFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

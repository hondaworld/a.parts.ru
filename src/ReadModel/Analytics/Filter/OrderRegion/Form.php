<?php


namespace App\ReadModel\Analytics\Filter\OrderRegion;


use App\Form\Type\DateIntervalPickerType;
use Symfony\Component\Form\Extension\Core\Type;
use App\ReadModel\User\UserFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private UserFetcher $userFetcher;

    public function __construct(UserFetcher $userFetcher)
    {
        $this->userFetcher = $userFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateofreport', DateIntervalPickerType::class, [])
            ->add('userID', Type\ChoiceType::class, [
                    'filter' => true,
                    'choices' => array_flip($this->userFetcher->assocOpt()),
                    'placeholder' => '']
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
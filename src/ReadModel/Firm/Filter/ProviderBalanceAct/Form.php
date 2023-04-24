<?php


namespace App\ReadModel\Firm\Filter\ProviderBalanceAct;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\User\UserFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;
    private UserFetcher $userFetcher;

    public function __construct(FirmFetcher $firmFetcher, UserFetcher $userFetcher)
    {
        $this->firmFetcher = $firmFetcher;
        $this->userFetcher = $userFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firmID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->firmFetcher->assocFromBalanceByProviderID($options['data']->providerID)),
                'placeholder' => false,
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('userID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->userFetcher->assocFromBalanceByProviderID($options['data']->providerID)),
                'placeholder' => false,
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('dateofadded', DateIntervalPickerType::class, []);
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
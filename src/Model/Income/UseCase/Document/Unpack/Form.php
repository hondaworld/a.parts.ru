<?php

namespace App\Model\Income\UseCase\Document\Unpack;


use App\Form\Type\FloatNumberType;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ProviderFetcher $providerFetcher;

    public function __construct(ProviderFetcher $providerFetcher)
    {
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('providerID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Поставщик',
                'choices' => array_flip($this->providerFetcher->assocIncomeInWarehouse()),
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

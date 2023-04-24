<?php

namespace App\Model\Card\UseCase\Card\Country;


use App\ReadModel\Contact\CountryFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CountryFetcher $countryFetcher;

    public function __construct(CountryFetcher $countryFetcher)
    {
        $this->countryFetcher = $countryFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('countryID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Страна',
                'choices' => array_flip($this->countryFetcher->assoc()),
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

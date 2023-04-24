<?php

namespace App\Model\Provider\UseCase\Provider\PriceCurrency;

use App\Form\Type\AutocompleteType;
use App\Form\Type\TimePickerType;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\UseCase\Provider\User;
use App\ReadModel\Finance\CurrencyFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $currencyFetcher;

    public function __construct(CurrencyFetcher $currencyFetcher)
    {

        $this->currencyFetcher = $currencyFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currencyID', Type\ChoiceType::class, [
                'label' => 'Валюта',
                'choices' => array_flip($this->currencyFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('koef', Type\TextType::class, ['label' => 'Коэффициент', 'attr' => ['class' => 'js-convert-float']])
            ->add('currencyOwn', Type\TextType::class, ['required' => false, 'label' => 'Собственный курс', 'attr' => ['class' => 'js-convert-float']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

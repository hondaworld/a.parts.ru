<?php


namespace App\ReadModel\Income\Filter\IncomeOrder;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapSkladFetcher $zapSkladFetcher;
    private ProviderFetcher $providerFetcher;

    public function __construct(ZapSkladFetcher $zapSkladFetcher, ProviderFetcher $providerFetcher)
    {
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('document_num', Type\TextType::class, ['filter' => true])
            ->add('zapSkladID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->zapSkladFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('providerID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->providerFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('dateofadded', DateIntervalPickerType::class, []);;
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
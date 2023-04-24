<?php


namespace App\ReadModel\Provider\Filter\Invoice;


use App\Form\Type\InPageType;
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
            ->add('inPage', InPageType::class)
            ->add('providerID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->providerFetcher->assoc()),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ]);
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
<?php


namespace App\ReadModel\Detail\Filter\ProviderExclude;


use App\Form\Type\InPageType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private ProviderFetcher $providerFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ProviderFetcher $providerFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('number', Type\TextType::class, ['filter' => true])
            ->add('createrID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->createrFetcher->assoc()),
                'attr' => [
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ])
            ->add('providerID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->providerFetcher->assoc()),
                'attr' => [
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
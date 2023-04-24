<?php

namespace App\Model\Detail\UseCase\ProviderExclude\Create;


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
            ->add('number', Type\TextType::class, ['label' => 'Номер', 'attr' => ['maxLength' => 30]])
            ->add('createrID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('comment', Type\TextType::class, ['required' => false, 'label' => 'Комментарий'])
            ->add('providerID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поставщик',
                'choices' => array_flip($this->providerFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Все'
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

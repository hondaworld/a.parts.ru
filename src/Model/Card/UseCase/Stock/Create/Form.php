<?php

namespace App\Model\Card\UseCase\Stock\Create;

use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ProviderFetcher $providerFetcher;

    public function __construct(ProviderFetcher $providerFetcher) {
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('text', Type\TextareaType::class, ['required' => false, 'label' => 'Описание'])
            ->add('providers', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поставщики',
                'choices' => array_flip($this->providerFetcher->assoc()),
                'expanded' => false,
                'multiple' => true,
                'attr' => ['class' => 'js-select2', 'size' => 1]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

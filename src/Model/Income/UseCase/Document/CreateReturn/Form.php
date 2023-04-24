<?php

namespace App\Model\Income\UseCase\Document\CreateReturn;


use App\Form\Type\IntegerNumberType;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;
    private ProviderFetcher $providerFetcher;

    public function __construct(FirmFetcher $firmFetcher, ProviderFetcher $providerFetcher)
    {
        $this->firmFetcher = $firmFetcher;
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('document_prefix', Type\TextType::class, ['required' => false, 'label' => 'Префикс', 'attr' => ['maxLength' => 15]])
            ->add('document_sufix', Type\TextType::class, ['required' => false, 'label' => 'Суфикс', 'attr' => ['maxLength' => 15]])
            ->add('firmID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Организация',
                'choices' => array_flip($this->firmFetcher->assocNotHide()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('providerID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Поставщик',
                'choices' => array_flip($this->providerFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('returning_reason', Type\TextType::class, ['required' => false, 'label' => 'Причина возврата'])
        ;

        foreach (array_keys($options['data']->incomeSklads) AS $incomeSkladID) {
            $builder->add('incomeSklad_' .$incomeSkladID, IntegerNumberType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

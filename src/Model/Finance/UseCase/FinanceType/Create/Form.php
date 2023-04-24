<?php

namespace App\Model\Finance\UseCase\FinanceType\Create;

use App\ReadModel\Firm\FirmFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $firmFetcher;

    public function __construct(FirmFetcher $firmFetcher)
    {
        $this->firmFetcher = $firmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('isMain', Type\CheckboxType::class, ['required' => false, 'label' => 'Основной вид оплаты', 'label_attr' => ['class' => 'switch-custom']])
            ->add('firmID', Type\ChoiceType::class, [
                'label' => 'Связанное предприятие',
                'choices' => array_flip($this->firmFetcher->assoc()),
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

<?php

namespace App\Model\Order\UseCase\Good\Create;


use App\Form\Type\IntegerNumberType;
use App\ReadModel\Order\OrderAddReasonFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private OrderAddReasonFetcher $orderAddReasonFetcher;

    public function __construct(OrderAddReasonFetcher $orderAddReasonFetcher)
    {
        $this->orderAddReasonFetcher = $orderAddReasonFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', Type\TextType::class, ['attr' => ['maxLength' => 50, 'placeholder' => 'Номер']])
            ->add('quantity', IntegerNumberType::class, ['attr' => ['style' => 'width: 50px;']])
            ->add('orderID', Type\HiddenType::class)
            ->add('createrID', Type\HiddenType::class)
            ->add('zapSkladID', Type\HiddenType::class)
            ->add('providerPriceID', Type\HiddenType::class)
        ;

        if (!$options['data']->orderID) {
            $builder->add('order_add_reasonID', Type\ChoiceType::class, [
                'required' => true,
                'choices' => array_flip($this->orderAddReasonFetcher->assoc()),
                'expanded' => true,
                'multiple' => false,
                'label_attr' => ['class' => 'radio-custom radio-inline']
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

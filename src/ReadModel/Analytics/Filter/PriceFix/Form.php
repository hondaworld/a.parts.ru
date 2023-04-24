<?php


namespace App\ReadModel\Analytics\Filter\PriceFix;


use App\Form\Type\InPageType;
use App\ReadModel\Sklad\PriceGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private PriceGroupFetcher $priceGroupFetcher;

    public function __construct(PriceGroupFetcher $priceGroupFetcher)
    {
        $this->priceGroupFetcher = $priceGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('price_groupID', Type\ChoiceType::class, [
                    'filter' => true,
                    'choices' => array_flip($this->priceGroupFetcher->assoc()),
                    'attr' => [
                        'onchange' => 'this.form.submit()'
                    ],
                    'placeholder' => '']
            )
            ->add('is_price_group_fix', Type\ChoiceType::class, [
                    'filter' => true,
                    'choices' => [
                        'да' => true,
                        'нет' => false
                    ],
                    'attr' => [
                        'onchange' => 'this.form.submit()'
                    ],
                    'placeholder' => '']
            );

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
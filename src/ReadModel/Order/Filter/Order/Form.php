<?php


namespace App\ReadModel\Order\Filter\Order;


use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher)
    {

        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('param', Type\HiddenType::class)
            ->add('orderID', Type\TextType::class, ['filter' => true, 'attr' => ['placeholder' => 'Номер заказа', 'class' => 'js-convert-number']])
            ->add('user', Type\TextType::class, ['filter' => true, 'attr' => ['placeholder' => 'Телефон, имя или организация']])
            ->add('number', Type\TextType::class, ['filter' => true, 'attr' => ['placeholder' => 'Номер детали']])
            ->add('createrID', Type\ChoiceType::class, ['filter' => true, 'choices' => array_flip($this->createrFetcher->assoc()), 'placeholder' => 'Бренд']);
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
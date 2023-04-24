<?php


namespace App\ReadModel\Order\Filter\ManagerOperation;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ManagerFetcher $managerFetcher;

    public function __construct(ManagerFetcher $managerFetcher)
    {
        $this->managerFetcher = $managerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('orderID', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => false, 'class' => 'js-convert-number']
            ])
            ->add('number', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => false]
            ])
            ->add('managerID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->managerFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('dateofadded', DateIntervalPickerType::class, []);
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
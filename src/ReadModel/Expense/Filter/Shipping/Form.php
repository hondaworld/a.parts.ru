<?php


namespace App\ReadModel\Expense\Filter\Shipping;


use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private ZapSkladFetcher $zapSkladFetcher;
    private ManagerFetcher $managerFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ZapSkladFetcher $zapSkladFetcher, ManagerFetcher $managerFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->managerFetcher = $managerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('managerID', Type\ChoiceType::class, ['filter' => true, 'choices' => array_flip($this->managerFetcher->assoc(true)), 'placeholder' => '', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('createrID', Type\ChoiceType::class, ['filter' => true, 'choices' => array_flip($this->createrFetcher->assoc()), 'placeholder' => '', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('zapSkladID_to', Type\ChoiceType::class, ['filter' => true, 'choices' => array_flip($this->zapSkladFetcher->assoc(true)), 'placeholder' => '', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('number', Type\TextType::class, ['filter' => true])
            ->add('isPacked', Type\ChoiceType::class, ['filter' => true, 'choices' => [
                'Все детали' => false,
                'Только детали в сборке' => true
            ], 'placeholder' => false, 'attr' => ['onchange' => 'this.form.submit()']])
        ;
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
<?php


namespace App\ReadModel\Expense\Filter\Income;


use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private ZapSkladFetcher $zapSkladFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ZapSkladFetcher $zapSkladFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->zapSkladFetcher = $zapSkladFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createrID', Type\ChoiceType::class, ['filter' => true, 'choices' => array_flip($this->createrFetcher->assoc()), 'placeholder' => '', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('zapSkladID', Type\ChoiceType::class, ['filter' => true, 'choices' => array_flip($this->zapSkladFetcher->assoc(true)), 'placeholder' => '', 'attr' => ['onchange' => 'this.form.submit()']])
            ->add('number', Type\TextType::class, ['filter' => true])
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
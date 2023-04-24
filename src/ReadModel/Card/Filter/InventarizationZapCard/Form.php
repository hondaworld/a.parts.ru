<?php


namespace App\ReadModel\Card\Filter\InventarizationZapCard;


use App\Form\Type\InPageType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapSkladFetcher $zapSkladFetcher;
    private CreaterFetcher $createrFetcher;

    public function __construct(
        ZapSkladFetcher  $zapSkladFetcher,
        CreaterFetcher   $createrFetcher
    )
    {
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('number', Type\TextType::class, ['filter' => true])
            ->add('location', Type\TextType::class, ['filter' => true])
            ->add('zapSkladID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->zapSkladFetcher->assoc()),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->createrFetcher->assoc()),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ]);
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
<?php


namespace App\ReadModel\Card\Filter\InventarizationGood;


use App\Form\Type\InPageType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ManagerFetcher $managerFetcher;
    private ZapSkladFetcher $zapSkladFetcher;
    private CreaterFetcher $createrFetcher;

    public function __construct(
        ManagerFetcher   $managerFetcher,
        ZapSkladFetcher  $zapSkladFetcher,
        CreaterFetcher   $createrFetcher
    )
    {
        $this->managerFetcher = $managerFetcher;
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('number', Type\TextType::class, ['filter' => true])
            ->add('location', Type\TextType::class, ['filter' => true])
            ->add('managerID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->managerFetcher->assoc(true)),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ])
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
            ])
            ->add('showDis', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Все' => false,
                    'Несоответствия' => true,
                ],
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt',
                    'onchange' => 'this.form.submit()'
                ]
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
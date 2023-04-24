<?php


namespace App\ReadModel\Analytics\Filter\PriceRegion;


use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapSkladFetcher $zapSkladFetcher;

    public function __construct(ZapSkladFetcher $zapSkladFetcher)
    {
        $this->zapSkladFetcher = $zapSkladFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('abc', Type\TextType::class, ['filter' => true, 'attr' => ['size' => 1, 'maxLength' => 1, 'placeholder' => 'ABC']])
            ->add('zapSkladID', Type\ChoiceType::class, [
                    'filter' => true,
                    'choices' => array_flip($this->zapSkladFetcher->assoc()),
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
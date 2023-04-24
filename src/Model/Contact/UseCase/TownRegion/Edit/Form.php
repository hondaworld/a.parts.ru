<?php

namespace App\Model\Contact\UseCase\TownRegion\Edit;


use App\ReadModel\Contact\CountryFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    private CountryFetcher $countryFetcher;

    public function __construct(CountryFetcher $countryFetcher) {

        $this->countryFetcher = $countryFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('countryID', Type\ChoiceType::class, ['label' => 'Страна', 'choices' => array_flip($this->countryFetcher->assoc())])
            ->add('name', Type\TextType::class, ['label' => 'Наименование', 'attr' => ['maxLength' => 50]])
            ->add('daysFromMoscow', Type\TextType::class, ['label' => 'Дней до Москвы', 'attr' => ['class' => 'js-convert-number']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

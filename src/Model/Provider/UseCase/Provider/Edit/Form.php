<?php

namespace App\Model\Provider\UseCase\Provider\Edit;

use App\Form\Type\AutocompleteType;
use App\Model\Provider\UseCase\Provider\User;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $zapSkladFetcher;

    public function __construct(ZapSkladFetcher $zapSkladFetcher)
    {

        $this->zapSkladFetcher = $zapSkladFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование', 'attr' => ['maxLength' => 50]])
            ->add('user', AutocompleteType::class, ['label' => 'Клиент', 'url' => '/api/users', 'data_class' => User::class])
            ->add('koef_dealer', Type\TextType::class, ['required' => false, 'label' => 'Процент от дилерской цены (если цена больше)', 'attr' => ['class' => 'js-convert-float'], 'help' => 'Задайте "0", если не учитывать'])
            ->add('isDealer', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Является дилером', 'label_attr' => ['class' => 'switch-custom']])
            ->add('zapSkladID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Приоритетный склад',
                'choices' => array_flip($this->zapSkladFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

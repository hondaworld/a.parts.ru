<?php

namespace App\Model\Auto\UseCase\MotoModel\Edit;


use App\ReadModel\Auto\MotoGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private MotoGroupFetcher $motoGroupFetcher;

    public function __construct(MotoGroupFetcher $motoGroupFetcher)
    {

        $this->motoGroupFetcher = $motoGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('moto_groupID', Type\ChoiceType::class, [
                'label' => 'Группа',
                'choices' => array_flip($this->motoGroupFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

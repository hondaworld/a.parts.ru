<?php

namespace App\Model\Card\UseCase\Card\Description;


use App\ReadModel\Card\ZapGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapGroupFetcher $zapGroupFetcher;

    public function __construct(ZapGroupFetcher $zapGroupFetcher)
    {
        $this->zapGroupFetcher = $zapGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', Type\TextareaType::class, ['required' => false, 'label' => 'Любая дополнительная информация о товаре'])
            ->add('text_fake', Type\TextareaType::class, ['required' => false, 'label' => 'Информация о подделках'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

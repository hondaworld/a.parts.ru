<?php

namespace App\Model\Order\UseCase\Good\Refuse;


use App\ReadModel\Shop\DeleteReasonFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private DeleteReasonFetcher $deleteReasonFetcher;

    public function __construct(DeleteReasonFetcher $deleteReasonFetcher)
    {
        $this->deleteReasonFetcher = $deleteReasonFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deleteReasonID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Причина отказа',
                'choices' => array_flip($this->deleteReasonFetcher->assoc()),
                'placeholder' => ''
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

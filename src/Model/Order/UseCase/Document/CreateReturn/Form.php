<?php

namespace App\Model\Order\UseCase\Document\CreateReturn;


use App\Form\Type\IntegerNumberType;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
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
            ->add('document_prefix', Type\TextType::class, ['required' => false, 'label' => 'Префикс', 'attr' => ['maxLength' => 15]])
            ->add('document_sufix', Type\TextType::class, ['required' => false, 'label' => 'Суфикс', 'attr' => ['maxLength' => 15]])
            ->add('zapSkladD', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Склад',
                'choices' => array_flip($this->zapSkladFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('returning_reason', Type\TextType::class, ['required' => false, 'label' => 'Причина возврата'])
        ;

        foreach (array_keys($options['data']->goods) AS $goodID) {
            $builder->add('goods_' . $goodID, IntegerNumberType::class, ['required' => false, 'attr' => ['class' => 'form-control-sm']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

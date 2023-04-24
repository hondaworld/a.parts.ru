<?php


namespace App\ReadModel\Detail\Filter\Creater;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher)
    {

        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('name', Type\TextType::class, ['filter' => true])
            ->add('isOriginal', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Да' => true,
                    'Нет' => false,
                ],
                'attr' => [
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
            ])
            ->add('tableName', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->createrFetcher->assocTableNames()),
                'attr' => [
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
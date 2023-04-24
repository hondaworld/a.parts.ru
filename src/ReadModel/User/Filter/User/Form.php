<?php


namespace App\ReadModel\User\Filter\User;


use App\Form\Type\InPageType;
use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ManagerFetcher $managerFetcher;

    public function __construct(ManagerFetcher $managerFetcher)
    {

        $this->managerFetcher = $managerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('phonemob', Type\TextType::class, ['filter' => true])
            ->add('name', Type\TextType::class, ['filter' => true])
            ->add('userName', Type\TextType::class, ['filter' => true])
            ->add('town', Type\TextType::class, ['filter' => true])
            ->add('isOpt', Type\ChoiceType::class, ['filter' => true, 'choices' => [
                'Нет' => false,
                'Да' => true
            ], 'placeholder' => ''
            ])
            ->add('isShowHide', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Активные' => false,
                    'Все' => true
                ], 'placeholder' => false,
                'label' => false
            ])
            ->add('ownerManagerID', Type\ChoiceType::class, ['filter' => true, 'choices' => array_flip($this->managerFetcher->assoc(true)), 'placeholder' => '']);
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
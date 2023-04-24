<?php


namespace App\ReadModel\Firm\Filter\AllDocuments;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\Model\Firm\Entity\Schet\Schet;
use App\ReadModel\Finance\FinanceTypeFetcher;
use App\ReadModel\Firm\FirmFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('dateofadded', DateIntervalPickerType::class, [])
            ->add('document_num', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 80px;']])
            ->add('from_name', Type\TextType::class, ['filter' => true])
            ->add('to_name', Type\TextType::class, ['filter' => true])
        ;
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
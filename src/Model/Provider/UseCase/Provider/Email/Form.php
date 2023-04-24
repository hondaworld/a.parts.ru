<?php

namespace App\Model\Provider\UseCase\Provider\Email;

use App\Form\Type\AutocompleteType;
use App\Model\Provider\UseCase\Provider\User;
use App\ReadModel\Sklad\ZapSkladFetcher;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
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
            ->add('incomeOrderNumber', Type\TextType::class, ['required' => false, 'label' => 'Первый номер заказа', 'attr' => ['class' => 'js-convert-number']])
            ->add('incomeOrderSubject', Type\TextType::class, ['required' => false, 'label' => 'Тема письма', 'attr' => ['maxLength' => 50]])
            ->add('incomeOrderText', CKEditorType::class, ['required' => false, 'label' => 'Текст письма'])
            ->add('incomeOrderSubject5', Type\TextType::class, ['required' => false, 'label' => 'Тема письма СПБ', 'attr' => ['maxLength' => 50]])
            ->add('incomeOrderText5', CKEditorType::class, ['required' => false, 'label' => 'Текст письма СПБ'])
            ->add('incomeOrderEmail', Type\TextType::class, ['required' => false, 'label' => 'E-mail'])
            ->add('isIncomeOrder', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Обрабатывать', 'label_attr' => ['class' => 'switch-custom'], 'help' => 'Если галочки нет, то по кнопке "Обработать" ничего не произойдет'])
;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

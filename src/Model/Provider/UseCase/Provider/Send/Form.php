<?php

namespace App\Model\Provider\UseCase\Provider\Send;

use App\Form\Type\AutocompleteType;
use App\Form\Type\TimePickerType;
use App\Model\Provider\Entity\Provider\Provider;
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
            ->add('isIncomeOrderAutoSend', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Автоматически рассылать заказы', 'label_attr' => ['class' => 'switch-custom']])
            ->add('incomeOrderTime', TimePickerType::class, ['required' => false, 'label' => 'Время'])
            ->add('incomeOrderWeekDays', Type\ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => array_flip(Provider::WEEKS),
                'label_attr' => ['class' => 'checkbox-custom pr-2'],
                'label' => 'Дни недели',
                'attr' => ['class' => 'form-row']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Firm\UseCase\Schet\UserContact;


use App\Form\Type\DatePickerType;
use App\Form\Type\FloatNumberType;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ContactFetcher $contactFetcher;

    public function __construct(ContactFetcher $contactFetcher)
    {
        $this->contactFetcher = $contactFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('exp_user_contactID', Type\ChoiceType::class, [
            'required' => true,
            'label' => 'Адрес',
            'choices' => array_flip($this->contactFetcher->assocAllByUser($options['data']->exp_user)),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

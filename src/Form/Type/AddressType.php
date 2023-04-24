<?php


namespace App\Form\Type;


use App\Model\Contact\UseCase\Contact\Town;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('town', AutocompleteType::class, ['label' => 'Город', 'url' => '/api/towns', 'data_class' => Town::class])
            ->add('zip', Type\TextType::class, ['label' => 'Индекс', 'required' => false])
            ->add('street', Type\TextType::class, ['label' => 'Улица', 'required' => false])
            ->add('house', Type\TextType::class, ['label' => 'Дом', 'required' => false])
            ->add('str', Type\TextType::class, ['label' => 'Строение (корпус)', 'required' => false])
            ->add('kv', Type\TextType::class, ['label' => 'Квартира (офис)', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

    public function getParent(): string
    {
        return Type\FormType::class;
    }

}
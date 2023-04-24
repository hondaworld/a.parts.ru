<?php


namespace App\ReadModel\Work\Filter\WorkGroup;


use App\Model\Work\Entity\Group\WorkGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isTO', Type\ChoiceType::class, [
                'required' => false,
                'choices' => array_flip(WorkGroup::TO),
                'attr' => ['onchange' => 'this.form.submit()'],
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
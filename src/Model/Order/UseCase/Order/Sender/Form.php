<?php

namespace App\Model\Order\UseCase\Order\Sender;

use App\Form\Type\AutocompleteFirmType;
use App\Model\Order\UseCase\Firm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firm', AutocompleteFirmType::class, [
                'required' => false,
                'label' => 'Предприятие',
                'data_class' => Firm::class,
                'contacts' => $options['data']->contacts,
                'beznals' => $options['data']->beznals,
                ]
            )
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

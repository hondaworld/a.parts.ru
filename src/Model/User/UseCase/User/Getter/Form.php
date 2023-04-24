<?php

namespace App\Model\User\UseCase\User\Getter;

use App\Form\Type\AutocompleteUserType;
use App\Model\User\UseCase\User\User;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
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
            ->add('user', AutocompleteUserType::class, [
                'required' => false,
                'label' => 'Клиент',
                'url' => '/api/users',
                'data_class' => User::class,
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

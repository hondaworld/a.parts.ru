<?php

namespace App\Model\User\UseCase\BalanceHistory\Attach;

use App\Form\Type\FileUploadType;
use App\ReadModel\Firm\FirmFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;

    public function __construct(FirmFetcher $firmFetcher)
    {
        $this->firmFetcher = $firmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('attach', FileUploadType::class, [
                'label' => 'Платежка',
                'delete_url' => 'users.balance.history.attach.delete',
                'delete_params' => ['userID' => $options['data']->userID, 'id' => $options['data']->balanceID],
                'delete_message' => 'Вы уверены, что хотите удалить платежку?',
                'is_vertical' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

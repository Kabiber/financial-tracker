<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('amount', NumberType::class, [
                'label' => 'Сумма',
                'scale' => 2,
                'required' => false,
                'attr' => ['placeholder' => 'Введите сумму'],
            ])
            ->add('description', TextType::class, [
                'label' => 'Описание',
                'required' => false,
                'attr' => ['placeholder' => 'Введите описание'],
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Дата',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['placeholder' => 'Выберите дату'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Категория',
                'placeholder' => 'Выберите категорию',
                'required' => true,
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('c.name', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
            'user' => null,
        ]);

        $resolver->setRequired('user');
    }
}
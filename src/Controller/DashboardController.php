<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionFilterType;
use App\Form\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(TransactionFilterType::class);
        $form->handleRequest($request);

        // Создаём QueryBuilder
        $qb = $entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->where('t.user = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('t.date', 'DESC')
            ->setMaxResults(10);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Фильтр по категории
            if ($data['category']) {
                $qb->andWhere('t.category = :category')
                    ->setParameter('category', $data['category']);
            }

            // Фильтр по доходам/расходам
            if ($data['isIncome'] !== null) {
                $qb->join('t.category', 'c')
                    ->andWhere('c.isIncome = :isIncome')
                    ->setParameter('isIncome', $data['isIncome']);
            }

            // Фильтр по датам
            if ($data['startDate']) {
                $qb->andWhere('t.date >= :startDate')
                    ->setParameter('startDate', $data['startDate']);
            }
            if ($data['endDate']) {
                $qb->andWhere('t.date <= :endDate')
                    ->setParameter('endDate', $data['endDate']);
            }
        }

        $transactions = $qb->getQuery()->getResult();

        $income = 0;
        $expenses = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->getCategory()->isIncome()) {
                $income += $transaction->getAmount();
            } else {
                $expenses += $transaction->getAmount();
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'transactions' => $transactions,
            'income' => $income,
            'expenses' => $expenses,
            'filter_form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard/new', name: 'transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $transaction = new Transaction();
        $transaction->setUser($this->getUser());
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($transaction);
            $entityManager->flush();

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $transactions = $entityManager->getRepository(Transaction::class)->findBy(
            ['user' => $this->getUser()],
            ['date' => 'DESC'],
            10
        );

        // Подсчёт доходов и расходов
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
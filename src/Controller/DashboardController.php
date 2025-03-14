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
use Knp\Component\Pager\PaginatorInterface;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(TransactionFilterType::class, null, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        $qb = $entityManager->getRepository(Transaction::class)
            ->createQueryBuilder('t')
            ->where('t.user = :user')
            ->setParameter('user', $this->getUser())
            ->orderBy('t.date', 'DESC');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['category']) {
                $qb->andWhere('t.category = :category')->setParameter('category', $data['category']);
            }
            if ($data['isIncome'] !== null) {
                $qb->join('t.category', 'c')->andWhere('c.isIncome = :isIncome')->setParameter('isIncome', $data['isIncome']);
            }
            if ($data['startDate']) {
                $qb->andWhere('t.date >= :startDate')->setParameter('startDate', $data['startDate']);
            }
            if ($data['endDate']) {
                $qb->andWhere('t.date <= :endDate')->setParameter('endDate', $data['endDate']);
            }
        }

        $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1), 5);

        $income = 0;
        $expenses = 0;
        $categoriesData = [];
        foreach ($pagination as $transaction) {
            $category = $transaction->getCategory();
            if ($category && $category->isIncome()) {
                $income += $transaction->getAmount();
            } else {
                $expenses += $transaction->getAmount();
            }
            $categoryName = $category ? $category->getName() : 'Без категории';
            $categoriesData[$categoryName] = ($categoriesData[$categoryName] ?? 0) + $transaction->getAmount();
        }

        return $this->render('dashboard/index.html.twig', [
            'pagination' => $pagination,
            'income' => $income,
            'expenses' => $expenses,
            'categories_data' => $categoriesData,
            'filter_form' => $form->createView(),
        ]);
    }

    #[Route('/dashboard/delete-transactions', name: 'dashboard_delete_transactions', methods: ['POST'])]
    public function deleteTransactions(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Получаем все параметры запроса как массив
        $postData = $request->request->all();
        $transactionIds = $postData['transactions'] ?? null;

        // Проверяем, что что-то выбрано
        if (!$transactionIds || !is_array($transactionIds) || empty($transactionIds)) {
            $this->addFlash('error', 'Выберите хотя бы одну транзакцию для удаления.');
            return $this->redirectToRoute('dashboard');
        }

        $transactions = $entityManager->getRepository(Transaction::class)->findBy([
            'id' => $transactionIds,
            'user' => $this->getUser(),
        ]);

        if (count($transactions) !== count($transactionIds)) {
            $this->addFlash('error', 'Некоторые транзакции не найдены или вам не принадлежат.');
            return $this->redirectToRoute('dashboard');
        }

        foreach ($transactions as $transaction) {
            $entityManager->remove($transaction);
        }
        $entityManager->flush();

        $this->addFlash('success', 'Выбранные транзакции успешно удалены.');
        return $this->redirectToRoute('dashboard');
    }

    #[Route('/dashboard/new', name: 'transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $transaction = new Transaction();
        $transaction->setUser($this->getUser());
        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($transaction);
                $entityManager->flush();
                $this->addFlash('success', 'Транзакция успешно создана.');
                return $this->redirectToRoute('dashboard');
            } else {
                // Выводим предупреждения для конкретных полей
                if (!$form->get('amount')->getData()) {
                    $this->addFlash('warning', 'Пожалуйста, укажите сумму транзакции.');
                }
                if (!$form->get('date')->getData()) {
                    $this->addFlash('warning', 'Пожалуйста, укажите дату транзакции.');
                }
            }
        }

        return $this->render('dashboard/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/dashboard/{id}/edit', name: 'transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Проверяем, что транзакция принадлежит текущему пользователю
        if ($transaction->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Вы не можете редактировать эту транзакцию.');
        }

        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(), // Передаём текущего пользователя
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Транзакция успешно обновлена.');
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('dashboard/edit.html.twig', [
            'form' => $form->createView(),
            'transaction' => $transaction,
        ]);
    }
}
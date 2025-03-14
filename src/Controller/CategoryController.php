<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $category = new Category();
        $category->setUser($this->getUser());
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Категория успешно создана.');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER'); // Проверяем, что пользователь авторизован

        // Проверяем, что категория принадлежит текущему пользователю
        if ($category->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Вы не можете редактировать эту категорию.');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Категория успешно обновлена.');
            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/category/{id}/delete', name: 'category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Проверяем, что категория принадлежит текущему пользователю
        if ($category->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Вы не можете удалить эту категорию.');
        }
        // Проверяем CSRF-токен
        if (!$this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Неверный CSRF-токен.');
            return $this->redirectToRoute('category_index');
        }
        // Проверяем, есть ли транзакции с этой категорией
        $transactionCount = $entityManager->getRepository(\App\Entity\Transaction::class)
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();

        if ($transactionCount > 0) {
            $this->addFlash('warning', sprintf('Нельзя удалить категорию "%s", так как она используется в %d транзакци%s.',
                $category->getName(),
                $transactionCount,
                $transactionCount === 1 ? 'и' : 'ях'
            ));
            return $this->redirectToRoute('category_index');
        }

        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash('success', 'Категория успешно удалена.');

        return $this->redirectToRoute('category_index');
    }
}
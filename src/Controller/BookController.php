<?php

namespace App\Controller;

use App\Entity\Book; // Assure-toi d'importer la classe Book
use App\Repository\BookRepository; // Assure-toi d'importer le repository de Book
use Doctrine\ORM\EntityManagerInterface; // Assure-toi d'importer EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book/list', name: 'app_book_list')]
    public function list(BookRepository $repository): Response
    {
        $books = $repository->findAll();

        return $this->render('book/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/book/add', name: 'app_book_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();

        // Vérifie si la requête est de type POST
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $published = $request->request->get('published') === 'true';

            // Vérifie que le titre est une chaîne valide
            if ($title !== null && is_string($title)) {
                $book->setTitle($title);
                $book->setPublished($published);

                // Persiste le livre dans la base de données
                $entityManager->persist($book);
                $entityManager->flush();

                return $this->redirectToRoute('app_book_list');
            } else {
                // Gérer le cas où le titre n'est pas valide
                // Vous pouvez choisir de rediriger vers la page d'ajout ou afficher un message d'erreur
                return $this->render('book/add.html.twig', [
                    'error' => 'Le titre doit être une chaîne valide.',
                ]);
            }
        }

        return $this->render('book/add.html.twig');
    }
    #[Route('/book/{id}/edit', name: 'app_book_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, BookRepository $bookRepository, EntityManagerInterface $entityManager): Response
    {
        // Trouver le livre par ID
        $book = $bookRepository->find($id);
    
        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé');
        }
    
        // Si la méthode est POST, traite la modification
        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $published = $request->request->get('published') === 'true';
    
            // Vérifiez si le titre n'est pas vide avant de le mettre à jour
            if ($title !== null) {
                $book->setTitle($title);
                $book->setPublished($published);
                $entityManager->flush();
    
                return $this->redirectToRoute('app_book_list');
            }
        }
    
        // Si la méthode est GET, affiche le formulaire d'édition
        return $this->render('book/edit.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/book/{id}/delete', name: 'app_book_delete', methods: ['POST'])]
    public function delete(int $id, BookRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $book = $repository->find($id);

        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé');
        }

        $entityManager->remove($book);
        $entityManager->flush();

        return $this->redirectToRoute('app_book_list');
    }
}

<?php

namespace App\Controller;

use App\Entity\Author; // Assure-toi d'importer la classe Author
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface; // Assure-toi d'importer EntityManagerInterface
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }

    #[Route('/author/lister', name: 'app_author_lister')]
    public function lister(AuthorRepository $repository): Response
    {
        $authors = $repository->findAll();

        return $this->render('author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    // Nouvelle méthode pour afficher le formulaire d'ajout
    #[Route('/author/ajouter', name: 'app_author_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Si la méthode est POST, traite le formulaire
        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $email = $request->request->get('email');

            if ($username && $email) {
                $author = new Author();
                $author->setUsername($username);
                $author->setEmail($email);

                $entityManager->persist($author);
                $entityManager->flush();

                return $this->redirectToRoute('app_author_lister');
            }
        }

        // Si la méthode est GET, affiche le formulaire
        return $this->render('author/add.html.twig');
    }

    #[Route('/author/{id}/modifier', name: 'app_author_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, AuthorRepository $authorRepository, EntityManagerInterface $entityManager): Response
    {
        $author = $authorRepository->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé');
        }

        // Si la méthode est POST, traite la modification
        if ($request->isMethod('POST')) {
            $username = $request->request->get('username');
            $email = $request->request->get('email');

            if ($username && $email) {
                $author->setUsername($username);
                $author->setEmail($email);

                $entityManager->flush();
                return $this->redirectToRoute('app_author_lister');
            }
        }

        // Si la méthode est GET, affiche le formulaire d'édition
        return $this->render('author/edit.html.twig', [
            'author' => $author,
        ]);
    }

    #[Route('/author/{id}/supprimer', name: 'app_author_delete', methods: ['POST'])]
    public function delete(int $id, AuthorRepository $authorRepository, EntityManagerInterface $entityManager): Response
    {
        $author = $authorRepository->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Auteur non trouvé');
        }

        $entityManager->remove($author);
        $entityManager->flush();

        return $this->redirectToRoute('app_author_lister');
    }
}

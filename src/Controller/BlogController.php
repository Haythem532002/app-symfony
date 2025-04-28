<?php

namespace App\Controller;

use App\Form\ArticleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use App\Entity\Article;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;

final class BlogController extends AbstractController
{
    private ArticleRepository $repo;

    public function __construct(ArticleRepository $repo)
    {
        $this->repo = $repo;
    }

    #[Route('/blog', name: 'app_blog')]
    public function index(): Response
    {
        $articles = $this->repo->findAll();
        $user = $this->getUser();
        $username = $user ? $user->getUsername() : null;

        if ($this->getUser()) {
            $this->addFlash('success', 'You are successfully logged in as ' . $this->getUser()->getUsername());
        }

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles,
            'username' => $username
        ]);
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('blog/home.html.twig');
    }


    #[Route('/blog/new', name: 'new_form')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $article->setCreatedat(new \DateTimeImmutable('tomorrow'));
        // $form = $this->createFormBuilder($article)
        //                 ->add('title', TextType::class)
        //                 ->add('content', TextType::class)
        //                 ->add('image', TextType::class)
        //                 ->add('save',SubmitType::class, ['label' => 'Create Article'])
        //                 ->getForm();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('app_blog');
        }
        return $this->render('/blog/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/blog/{id}', name: 'blog_show')]
    public function show($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = $this->repo->find($id);

        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setCreatedat(new \DateTimeImmutable());

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('blog_show', ['id' => $id]);
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/blog/delete/{id}', name: 'blog_delete')]
    public function delete($id, EntityManagerInterface $entityManager): Response
    {
        $article = $this->repo->find($id);

        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }

        $entityManager->remove($article);
        $entityManager->flush();
        return $this->redirectToRoute('app_blog');
    }

    #[Route('/blog/edit/{id}', name: 'blog_edit')]
    public function edit($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = $this->repo->find($id);

        if (!$article) {
            throw $this->createNotFoundException('The article does not exist');
        }
        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('content', TextType::class)
            ->add('image', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Update Article'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('app_blog');
        }
        return $this->render('/blog/edit.html.twig', [
            'form' => $form
        ]);

    }
}

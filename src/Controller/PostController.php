<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Image;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('image')->getData();
            foreach ($images as $image) {
                // génère un nouveau nom du fichier
                $file = md5(uniqid()) . '.' . $image->guessExtension();
                // copier l'image dans le dossier uploads
                $image->move($this->getParameter('images_directory'), $file);
                // stocker le nom de l'image dans la bdd
                $img = new Image();
                $img->setName($file);
                $post->addImage($img);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute(
                'post_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET"})
     */
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('image')->getData();
            foreach ($images as $image) {
                // génère un nouveau nom du fichier
                $file = md5(uniqid()) . '.' . $image->guessExtension();
                // copier l'image dans le dossier uploads
                $image->move($this->getParameter('images_directory'), $file);
                // stocker le nom de l'image dans la bdd
                $img = new Image();
                $img->setName($file);
                $post->addImage($img);
            }
            $this->getDoctrine()
                ->getManager()
                ->flush();

            return $this->redirectToRoute(
                'post_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if (
            $this->isCsrfTokenValid(
                'delete' . $post->getId(),
                $request->request->get('_token')
            )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute(
            'post_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    /**
     * Delete one image of post
     *
     * @return void
     * @Route("/delete/image/{id}", name="post_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Image $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if (
            $this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])
        ) {
            // On récupère le nom de l'image
            $name = $image->getName();
            // On supprime le fichier physiquement
            unlink($this->getParameter('images_directory') . '/' . $name);

            // On supprime l'entrée de la base
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}

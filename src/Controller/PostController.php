<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    /**
     * @Route("/registrar_post", name="RegistrarPost")
     */
    public function index(Request $request, SluggerInterface $slugger): Response {
        $post = new Post();
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            //PREPARANDO IMAGEN
            $brochureFile = $form->get('foto')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('imagenes_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                   throw new \Exception('¡Ups! Ha ocurrido un error, sorry');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $post->setFoto($newFilename);
            }
            //OBTENER EL USUARIO
            $user = $this->getUser();
            $post->setUser($user);
            //GUARDANDO EL POST EN LA BASE DE DATOS
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('dash_board');
        }
        return $this->render('post/index.html.twig', [
           'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/Post/{id}", name="VerPost")
     */
    public function VerPost($id){
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($id);
        return $this->render('post/verPost.html.twig',['post'=>$post]);
    }
    /**
     * @Route("/mis_post", name="MisPost")
     */
    public function MisPost(){
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $posts = $em->getRepository(Post::class)->findBy(['user'=>$user]);
        return $this->render('post/MisPost.html.twig',['posts'=>$posts]);

    }
}

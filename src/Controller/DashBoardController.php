<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comentarios;
use App\Entity\Post;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashBoardController extends AbstractController
{
    /**
     * @Route("/", name="dash_board")
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        //COMPROBAR QUE HAYA UN USUARIO CONECTADO
        $user = $this->getUser();
        if ($user){
            //TRAER LOS POST DE LA BASE DE DATOS
            $em = $this->getDoctrine()->getManager();
            $query = $em->getRepository(Post::class)->BuscarTodosPost();
            //$post = $em->getRepository(Post::class)->findAll();
            //TRAER LOS COMENTARIOS DEL USUARIO
            $comentarios = $em->getRepository(Comentarios::class)->ComentariosUsuario($user->getId());
            //PAGINADOR
            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                2 /*limit per page*/
            );

            return $this->render('dash_board/index.html.twig', [
                'pagination' => $pagination,
                'comentarios' => $comentarios
            ]);
        }else{
            return $this->redirectToRoute('app_login');
        }

    }
}

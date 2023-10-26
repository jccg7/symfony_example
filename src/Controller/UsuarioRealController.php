<?php

namespace App\Controller;


use App\Entity\UsuarioReal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/usuario/real', name: 'app_usuario_real')]
class UsuarioRealController extends AbstractController
{
    
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UsuarioRealController.php',
        ]);
    }

    
      #[Route('', name: 'app_usuario_real_create', methods: ['POST'])]
      public function create(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
      {
        $usuarioReal = new UsuarioReal();
        $usuarioReal->setEmail($request->request->get('email'));
        $plainPassword = $request->request->get('password');
        $hashedPassword = $passwordHasher->hashPassword($usuarioReal, $plainPassword);
        $usuarioReal->setPassword($hashedPassword);
        // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
        $entityManager->persist($usuarioReal);
    
        // Se ejecutan las consultas SQL para guardar el nuevo registro
        $entityManager->flush();
    
        return $this->json([
            'message' => 'Se guardo el nuevo usuario real con id ' . $usuarioReal->getId()
        ]); 
      }
    

}

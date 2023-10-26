<?php

namespace App\Controller;

use App\Service\GeneradorDeMensajes;
use App\Entity\Direccion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DireccionController extends AbstractController
{
    #[Route('/direccion', name: 'app_direccion')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DireccionController.php',
        ]);
    }

    #[Route('', name: 'app_direccion_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $direccion = new Direccion();
    $direccion->setDepartamento($request->request->get('departamento'));
    $direccion->setMunicipio($request->request->get('municipio'));
    $direccion->setDirection($request->request->get('direccion'));
    // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
    $entityManager->persist($direccion);

    // Se ejecutan las consultas SQL para guardar el nuevo registro
    $entityManager->flush();

    return $this->json([
        'message' => $generadorDeMensajes->getMensaje() .'Se guardo la nueva direccion con id ' . $direccion->getId()
    ]); 
  }

  #[Route('', name: 'app_direccion_read_all', methods: ['GET'])]
  public function readAll(EntityManagerInterface $entityManager, Request $request): JsonResponse
  {
    $repositorio = $entityManager->getRepository(Direccion::class);

    $limit = $request->get('limit', 5);

    $page = $request->get('page', 1);

    $direcciones = $repositorio->findAllWithPagination($page, $limit);

    $total = $direcciones->count();

    $lastPage = (int) ceil($total/$limit);

    $data = [];
  
    foreach ($direcciones as $direccion) {
        $data[] = [
            'id' => $direccion->getId(),
            'departamento' => $direccion->getDepartamento(),
            'municipio' => $direccion->getMunicipio(),
            'direction' => $direccion->getDirection(),
        ];
    }
    
    return $this->json(['data' => $data, 'total' => $total, 'lastPage'=> $lastPage] ); 
  }

  #[Route('/{id}', name: 'app_direccion_read_one', methods: ['GET'])]
  public function readOne(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $direccion = $entityManager->getRepository(Direccion::class)->find($id);

    if(!$direccion){
      return $this->json(['error'=> $generadorDeMensajes->getMensaje() .'No se encontro la direccion.'], 404);
    }

    return $this->json([
      'id' => $direccion->getId(), 
      'departamento' => $direccion->getDepartamento(), 
      'municipio' => $direccion->getMunicipio(), 
      'direccion' => $direccion->getDirection()
    ]);  
  }

  #[Route('/{id}', name: 'app_direccion_edit', methods: ['PUT'])]
  public function update(EntityManagerInterface $entityManager, int $id, Request $request, GeneradorDeMensajes $generadorDeMensajes ): JsonResponse
  {

    // Busca el usuario por id
    $direccion = $entityManager->getRepository(Direccion::class)->find($id);

    // Si no lo encuentra responde con un error 404
    if (!$direccion) {
      return $this->json(['error'=> $generadorDeMensajes->getMensaje() .'No se encontro la direccion con id: '.$id], 404);
    }

    // Obtiene los valores del body de la request
    $departamento = $request->request->get('departamento');
    $municipio = $request->request->get('municipio');
    $direction = $request->request->get('direccion');

    // Si no envia uno responde con un error 422
    if ($departamento == null || $municipio == null || $direction == null){
      return $this->json(['error'=>'Se debe enviar el departamento, municipio y direccion de la direccion.'], 422);
    }

    // Se actualizan los datos a la entidad
    $direccion->setNombre($departamento);
    $direccion->setEdad($municipio);
    $direccion->setEdad($direction);

    $data=['id' => $direccion->getId(), 'departamento' => $direccion->getDepartamento(), 'municipio' => $direccion->getMunicipio(), 'direccion' => $direccion->getDirection()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json(['message'=> $generadorDeMensajes->getMensaje() .'Se actualizaron los datos de la direccion.', 'data' => $data]);
  }

  #[Route('/{id}', name: 'app_direccion_delete', methods: ['DELETE'])]
  public function delete(EntityManagerInterface $entityManager, int $id, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {

    // Busca la direccion por id
    $direccion= $entityManager->getRepository(Direccion::class)->find($id);

    // Si no lo encuentra responde con un error 404
    if (!$direccion) {
      return $this->json(['error'=> $generadorDeMensajes->getMensaje() .'No se encontro la direccion con el id: '.$id], 404);
    }

    // Remueve la entidad
    $entityManager->remove($direccion);

    $data=['id' => $direccion->getId(), 'departamento' => $direccion->getDepartamento(), 'edad' => $direccion->getMunicipio(), 'edad' => $direccion->getDirection()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json(['message'=> $generadorDeMensajes->getMensaje() .'Se elimino la direccion.', 'data' => $data]);
  }
    
}

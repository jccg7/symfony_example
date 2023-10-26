<?php

namespace App\Controller;

use App\Service\GeneradorDeMensajes;
use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EntidadController extends AbstractController
{
    #[Route('/entidad', name: 'app_entidad')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EntidadController.php',
        ]);
    }

    #[Route('', name: 'app_producto_create', methods: ['POST'])]
    public function create(EntityManagerInterface $entityManager, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $producto = new Producto();
    $producto->setNombre($request->request->get('nombre'));
    $producto->setPrecio($request->request->get('precio'));
    $producto->setExistencia($request->request->get('existencia'));
    // Se avisa a Doctrine que queremos guardar un nuevo registro pero no se ejecutan las consultas
    $entityManager->persist($producto);

    // Se ejecutan las consultas SQL para guardar el nuevo registro
    $entityManager->flush();

    return $this->json([
        'message' => $generadorDeMensajes->getMensaje() .'Se guardo el nuevo producto con id ' . $producto->getId()
    ]); 
  }

  #[Route('', name: 'app_producto_read_all', methods: ['GET'])]
  public function readAll(EntityManagerInterface $entityManager, Request $request): JsonResponse
  {
    $repositorio = $entityManager->getRepository(Producto::class);

    $limit = $request->get('limit', 5);

    $page = $request->get('page', 1);

    $productos = $repositorio->findAllWithPagination($page, $limit);

    $total = $productos->count();

    $lastPage = (int) ceil($total/$limit);

    $data = [];
  
    foreach ($productos as $producto) {
        $data[] = [
            'id' => $producto->getId(),
            'nombre' => $producto->getNombre(),
            'precio' => $producto->getPrecio(),
            'existencia' => $producto->getExistencia(),
        ];
    }
    
    return $this->json(['data' => $data, 'total' => $total, 'lastPage'=> $lastPage] ); 
  }

  #[Route('/{id}', name: 'app_producto_read_one', methods: ['GET'])]
  public function readOne(EntityManagerInterface $entityManager, int $id, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {
    $producto = $entityManager->getRepository(Usuario::class)->find($id);

    if(!$producto){
      return $this->json(['error'=> $generadorDeMensajes->getMensaje() .'No se encontro el producto.'], 404);
    }

    return $this->json([
      'id' => $producto->getId(), 
      'nombre' => $producto->getNombre(), 
      'precio' => $producto->getPrecio(), 
      'existencia' => $producto->getExistencia()
    ]);  
  }

  #[Route('/{id}', name: 'app_Producto_edit', methods: ['PUT'])]
  public function update(EntityManagerInterface $entityManager, int $id, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {

    // Busca el producto por id
    $producto = $entityManager->getRepository(Producto::class)->find($id);

    // Si no lo encuentra responde con un error 404
    if (!$producto) {
      return $this->json(['error'=> $generadorDeMensajes->getMensaje() .'No se encontro el producto con id: '.$id], 404);
    }

    // Obtiene los valores del body de la request
    $nombre = $request->request->get('nombre');
    $precio = $request->request->get('precio');
    $existencia = $request->request->get('existencia');

    // Si no envia uno responde con un error 422
    if ($nombre == null || $precio == null || $existencia = null){
      return $this->json(['error'=>'Se debe enviar el nombre, precio y existencia del producto.'], 422);
    }

    // Se actualizan los datos a la entidad
    $producto->setNombre($nombre);
    $producto->setPrecio($precio);
    $producto->setExistencia($existencia);

    $data=['id' => $producto->getId(), 'nombre' => $producto->getNombre(), 'precio' => $producto->getPrecio(), 'existencia' => $producto->getExistencia()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json(['message'=> $generadorDeMensajes->getMensaje() .'Se actualizaron los datos del producto.', 'data' => $data]);
  }

  #[Route('/{id}', name: 'app_producto_delete', methods: ['DELETE'])]
  public function delete(EntityManagerInterface $entityManager, int $id, Request $request, GeneradorDeMensajes $generadorDeMensajes): JsonResponse
  {

    // Busca el producto por id
    $producto = $entityManager->getRepository(Producto::class)->find($id);

    // Si no lo encuentra responde con un error 404
    if (!$producto) {
      return $this->json(['error'=> $generadorDeMensajes->getMensaje() .'No se encontro el producto con id: '.$id], 404);
    }

    // Remueve la entidad
    $entityManager->remove($producto);

    $data=['id' => $producto->getId(), 'nombre' => $producto->getNombre(), 'precio' => $producto->getPrecio(), 'precio' => $producto->getExistencia()];

    // Se aplican los cambios de la entidad en la bd
    $entityManager->flush();

    return $this->json(['message'=> $generadorDeMensajes->getMensaje() .'Se elimino el producto.', 'data' => $data]);
  }

}

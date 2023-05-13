<?php


// src/Controller/ProductController.php
namespace App\Controller;


// ...
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/',name:"default")]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
    $prod = new Product();
    $form = $this->createForm(ProductType::class, $prod);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()){
        $entityManager->persist($prod);
        $entityManager->flush();
    }
    return $this->render('product/index.html.twig', [
        'form' => $form->createView()
    ]);
    }
    #[Route('/products/', name: 'get_products')]
    public function getProducts(EntityManagerInterface $entityManager){
        $sql = "SELECT * from Product";
   

        $con=$entityManager->getConnection();
        $Product=$con->executeQuery($sql)->fetchAllAssociative();
        return new JsonResponse($Product
        );
    }
    #[Route('/product/{id}', name: 'get_product')]
    public function getProduct(EntityManagerInterface $entityManager,$id){
        $product=$entityManager->getRepository(Product::class)->find($id);
        return new JsonResponse([
            'id'=>$product->getId(),
            'name'=>$product->getName()
        ]);
    }

    #[Route('/product/add', name: 'add_product',methods:['POST'])]
    public function add_product(EntityManagerInterface $entityManager,Request $request): Response
    {
        $data= json_decode($request->getContent(),true);
        //dd($data);
        $product = new Product();
        $product->setName($data['test']);
        $product->setPrice(1999);
        $product->setDescription($data['desc']);

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new product with id '.$product->getId());
    }

    #[Route('/product/delete/{id}', name: 'deleteproduct', methods: ['DELETE'])]
    public function deleteproduct(product $id, EntityManagerInterface $em): JsonResponse 
    {
        $em->remove($id);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    #[Route('product/update/{id}',name:'updateProduct',methods:['PUT'])]
    public function updateProduct(int $id,Request $request,ProductRepository $rp, EntityManagerInterface $em) {
        $data= json_decode($request->getContent(),true);
        //$Product=$rp->find($id);
        $req="update Product set 
                name='".$data['name']."',
                description='".$data['description']."',
                price='".$data['price']."'

                where id=$id
            ";
        // $Product->setName($data["name"]);
        // $Product->setDescription($data["description"]);
        // $Product->setPrice($data["price"]);

        // $em->persist($Product);
        // $em->flush();
        $con=$em->getConnection();
        $con->executeQuery($req);
        return new JsonResponse("Product updated succeful");
        
    
    
        
    
       

    }
}
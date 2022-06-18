<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Cocur\Slugify\Slugify;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/admin/products', name: 'admin_product_index')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $product = new Product;
        $products = $productRepository->findBy([], ['sort' => 'ASC']);
        $categories = $categoryRepository->findBy([], ['sort' => 'ASC']);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugify = new Slugify();
            $product->setSlug($slugify->slugify($product->getName()));
            $product->setPrice($product->getPrice()*100);
            $manager->persist($product);
            $manager->flush();
        }

        $blankProduct = new Product;
        $blankProduct->setTrash(false);

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'categories' => $categories,
            'products' => $products,
            'currentProduct' => $blankProduct,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/product/{slug}', name: 'admin_product_edit')]
    public function edit($slug, ProductRepository $productRepository, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $productUrl = $productRepository->findOneBy(['slug' => $slug]);
        // Test si slug non enregistré
        
        $productRequest = new Product;
        
        $form = $this->createForm(ProductType::class, $productRequest);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $slugify = new Slugify();
            $productUrl->setName($productRequest->getName());
            $productUrl->setSlug($slugify->slugify($productRequest->getName()));
            $productUrl->setDescription($productRequest->getDescription());
            $productUrl->setSort($productRequest->getSort());
            $productUrl->setTrash($productRequest->isTrash());
            $productUrl->setCategory($productRequest->getCategory());
            $productUrl->setPrice($productRequest->getPrice() * 100);
            $manager->persist($productUrl);
            $manager->flush();
            return $this->redirectToRoute('admin_product_index');
        }

        $categories = $categoryRepository->findBy([], ['sort' => 'ASC']);
        
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'categories' => $categories,
            'currentProduct' => $productUrl,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/product/delete/{slug}', name: 'admin_product_delete')]
    public function delete($slug, ProductRepository $productRepository, EntityManagerInterface $manager): Response
    {
        $productUrl = $productRepository->findOneBy(['slug' => $slug]);
        // Test si slug non enregistré

        $manager->remove($productUrl);
        $manager->flush();

        return $this->redirectToRoute('admin_product_index');
    }
}

<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Cocur\Slugify\Slugify;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/admin/categories', name: 'admin_category_index')]
    public function index(CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $category = new Category;
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugify = new Slugify();
            $category->setSlug($slugify->slugify($category->getName()));
            $manager->persist($category);
            $manager->flush();
        }

        $blankCategory = new Category;
        $blankCategory->setTrash(false);
        
        $categories = $categoryRepository->findBy([], ['sort' => 'ASC']);
        
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
            'currentCategory' => $blankCategory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/categorie/{slug}', name: 'admin_category_edit')]
    public function edit($slug, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $categoryUrl = $categoryRepository->findOneBy(['slug' => $slug]);
        // Test si slug non enregistré
        
        $categoryRequest = new Category;
        
        $form = $this->createForm(CategoryType::class, $categoryRequest);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $changed = false;
            if ($categoryRequest->getName() != null && $categoryRequest->getName() != $categoryUrl->getName()) {
                $slugify = new Slugify();
                $categoryUrl->setName($categoryRequest->getName());
                $categoryUrl->setSlug($slugify->slugify($categoryRequest->getName()));
                $changed = true;
            }
            if ($categoryRequest->getSort() != $categoryUrl->getSort()) {
                $categoryUrl->setSort($categoryRequest->getSort());
                $changed = true;
            }
            if ($categoryRequest->isTrash() != $categoryUrl->isTrash()) {
                $categoryUrl->setTrash($categoryRequest->isTrash());
                $changed = true;
            }
            if ($changed) {
                $manager->persist($categoryUrl);
                $manager->flush();
            }
            return $this->redirectToRoute('admin_category_index');
        }

        $categories = $categoryRepository->findBy([], ['sort' => 'ASC']);
        
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories,
            'currentCategory' => $categoryUrl,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/categorie/delete/{slug}', name: 'admin_category_delete')]
    public function delete($slug, CategoryRepository $categoryRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $categoryUrl = $categoryRepository->findOneBy(['slug' => $slug]);
        // Test si slug non enregistré

        $manager->remove($categoryUrl);
        $manager->flush();

        return $this->redirectToRoute('admin_category_index');
    }
}

<?php

namespace App\Controller;

use App\Entity\Allergen;
use App\Repository\AllergenRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    #[Route('/menu', name: 'menu')]
    public function index(CategoryRepository $categoryRepository, AllergenRepository $allergenRepository): Response
    {
        $categories = $categoryRepository->findBy([], ['sort' => 'ASC']);
        $allergens = $allergenRepository->findBy([], ['sort' => 'ASC']);

        return $this->render('menu/index.html.twig', [
            'controller_name' => 'MenuController',
            'categories' => $categories,
            'allergens' => $allergens,
        ]);
    }

    #[Route('/menu/filter/{slug}', name: 'menu_filter')]
    public function filter($slug, AllergenRepository $allergenRepository, EntityManagerInterface $manager): Response
    {
        $allergen = $allergenRepository->findOneBy(['slug' => $slug]);
        if ($allergen->isFilter() == null) {
            $allergen->setFilter(true);
        } else {
            $allergen->setFilter(!$allergen->isFilter());
        }

        $manager->persist($allergen);
        $manager->flush();

        return $this->redirectToRoute('menu');
    }
}

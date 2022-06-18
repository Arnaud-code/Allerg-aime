<?php

namespace App\Controller;

use App\Entity\Allergen;
use App\Form\AllergenType;
use Cocur\Slugify\Slugify;
use App\Repository\AllergenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AllergenController extends AbstractController
{
    #[Route('/admin/allergens', name: 'admin_allergen_index')]
    public function index(AllergenRepository $allergenRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $allergen = new Allergen;
        
        $form = $this->createForm(AllergenType::class, $allergen);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slugify = new Slugify();
            $allergen->setSlug($slugify->slugify($allergen->getName()));
            $manager->persist($allergen);
            $manager->flush();
        }

        $blankAllergen = new Allergen;

        $allergens = $allergenRepository->findBy([], ['sort' => 'ASC']);

        return $this->render('allergen/index.html.twig', [
            'controller_name' => 'AllergenController',
            'allergens' => $allergens,
            'currentAllergen' => $blankAllergen,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/allergen/{slug}', name: 'admin_allergen_edit')]
    public function edit($slug, AllergenRepository $allergenRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $allergenUrl = $allergenRepository->findOneBy(['slug' => $slug]);
        // Test si slug non enregistré
        
        $allergenRequest = new Allergen;
        
        $form = $this->createForm(AllergenType::class, $allergenRequest);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $slugify = new Slugify();
            $allergenUrl->setName($allergenRequest->getName());
            $allergenUrl->setSlug($slugify->slugify($allergenRequest->getName()));
            $allergenUrl->setDescription($allergenRequest->getDescription());
            $allergenUrl->setSort($allergenRequest->getSort());
            $manager->persist($allergenUrl);
            $manager->flush();
            return $this->redirectToRoute('admin_allergen_index');
        }

        $allergens = $allergenRepository->findBy([], ['sort' => 'ASC']);
        
        return $this->render('allergen/index.html.twig', [
            'controller_name' => 'AllergenController',
            'allergens' => $allergens,
            'currentAllergen' => $allergenUrl,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/allergen/delete/{slug}', name: 'admin_allergen_delete')]
    public function delete($slug, AllergenRepository $allergenRepository, Request $request, EntityManagerInterface $manager): Response
    {
        $allergenUrl = $allergenRepository->findOneBy(['slug' => $slug]);
        // Test si slug non enregistré

        $manager->remove($allergenUrl);
        $manager->flush();

        return $this->redirectToRoute('admin_allergen_index');
    }
}

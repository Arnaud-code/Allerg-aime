<?php

namespace App\Controller;

use App\Entity\Presence;
use App\Repository\ProductRepository;
use App\Repository\AllergenRepository;
use App\Repository\CategoryRepository;
use App\Repository\PresenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PresenceController extends AbstractController
{
    #[Route('/admin/presences', name: 'admin_presence_index')]
    public function index(AllergenRepository $allergenRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $allergens = $allergenRepository->findBy([], ['sort' => 'ASC']);
        $categories = $categoryRepository->findBy([], ['sort' => 'ASC']);
        $products = $productRepository->findBy([], ['sort' => 'ASC']);

        return $this->render('presence/index.html.twig', [
            'controller_name' => 'PresenceController',
            'allergens' => $allergens,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    #[Route('/admin/presence/add/{pslug}/{aslug}', name: 'admin_presence_add')]
    public function add($pslug, $aslug, ProductRepository $productRepository, AllergenRepository $allergenRepository, EntityManagerInterface $manager): Response
    {
        $product = $productRepository->findOneBy(['slug' => $pslug]);
        $allergen = $allergenRepository->findOneBy(['slug' => $aslug]);

        $presence = new Presence;
        $presence->setProduct($product)->setAllergen($allergen);

        $manager->persist($presence);
        $manager->flush();

        return $this->redirectToRoute('admin_presence_index');
    }

    #[Route('/admin/presence/delete/{id}', name: 'admin_presence_delete')]
    public function delete($id, PresenceRepository $presenceRepository, EntityManagerInterface $manager): Response
    {
        $presence = $presenceRepository->find($id);

        $manager->remove($presence);
        $manager->flush();

        return $this->redirectToRoute('admin_presence_index');
    }
}

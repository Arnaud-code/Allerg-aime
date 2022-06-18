<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        // $breadcrumb = [['name' => 'home', 'text' => 'Accueil']];
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            // 'breadcrumb' => $breadcrumb,
        ]);
    }
}

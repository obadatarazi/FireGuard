<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/', name: 'home')]
    public function index(Request $request): Response
    {
        return $this->json([
            'result' => 'Hello world'
        ]);
    }
}

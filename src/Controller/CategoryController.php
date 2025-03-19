<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/category')]
class CategoryController extends AbstractController
{
    // #[Route(name: 'app_category')]
    // public function index(): Response
    // {
    //     return $this->render('category/index.html.twig', [
    //         'controller_name' => 'CategoryController',
    //     ]);
    // }

    #[Route('/{id}',name: 'category_show')]
    public function category_show($id,CategoryRepository $cr): Response
    {
        $category = $cr->find($id);
        $recipes = $category->getRecipe();
        return $this->render('category/show.html.twig',['recipes'=>$recipes,'category'=>$category]);
    }
}

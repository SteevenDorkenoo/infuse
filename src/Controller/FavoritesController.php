<?php

namespace App\Controller;

use App\Entity\Favorites;
use App\Entity\Recipes;
use App\Repository\FavoritesRepository;
// use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/recipes')]
class FavoritesController extends AbstractController
{
    #[Route('/favorites', name: 'app_favorites')]
    public function index(): Response
    {
        return $this->render('favorites/index.html.twig', [
            'controller_name' => 'FavoritesController',
        ]);
    }

    #[Route('{id}/favorites/new', name: 'favorites_new',methods: ['GET','POST'])]
    public function fav_new(Request $request, EntityManagerInterface $em, Recipes $recipe): Response
    {
        $fav = new Favorites;
        if($request->isMethod('POST'))
        {
            $fav->setDate(new \DateTime('now'));
            $fav->setUserId($this->getUser());
            $fav->setRecipeId($recipe);//a faire

            $em->persist($fav); // Prépare le favoris à être sauvegardée dans la base de données
            $em->flush();
        }
        return $this->redirectToRoute('show_recipe');
    }

    #[Route('{id}/favorites/delete', name: 'favorites_delete',methods: ['GET','POST'])]
    public function fav_delete($id,Favorites $fav,Request $request, EntityManagerInterface $em): Response
    {
        if($request->isMethod('POST'))
        {
            $em->remove($fav); // Supprime une recette de la base de données
            $em->flush(); // Sauvegarde la suppression dans la base de données
        }
        return $this->redirectToRoute('show_recipe',['id'=> $id]);
    }
}

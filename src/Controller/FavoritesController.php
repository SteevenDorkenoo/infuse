<?php

namespace App\Controller;

use App\Entity\Favorites;
use App\Entity\Recipes;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\EndorsementRepository;
use App\Repository\FavoritesRepository;
use App\Repository\RecipesRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/recipes')]
class FavoritesController extends AbstractController
{
    // #[Route('/favorites', name: 'app_favorites')]
    // public function index(): Response
    // {
    //     return $this->render('favorites/index.html.twig', [
    //         'controller_name' => 'FavoritesController',
    //     ]);
    // }

    #[Route('/favorites', name: 'favorites_index', methods: ['GET', 'POST'])]
    public function allFavorites(Request $request, RecipesRepository $repository, CategoryRepository $cr, EndorsementRepository $er): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    
        $user = $this->getUser();
    
        if (!$user instanceof User) {
            throw new \Exception('User is not authenticated or not an instance of User');
        }
    
        $userId = $user->getId();
    
        $recipes = $repository->findRecipesByFavoriteUserId($userId);
        $categories = $cr->findAll();
    
        // Tableau pour stocker les recettes avec leur différence de votes
        $recipesWithVotes = [];
    
        foreach ($recipes as $recipe) {
            $endorsements = $er->findBy(['recipe_id' => $recipe]);
            $positiveVotes = 0;
            $negativeVotes = 0;
    
            foreach ($endorsements as $endorsement) {
                if ($endorsement->isVote()) {
                    $positiveVotes++;
                } else {
                    $negativeVotes++;
                }
            }
    
            // Ajouter la recette et sa différence de votes au tableau
            $recipesWithVotes[] = [
                'recipe' => $recipe,
                'voteDifference' => $positiveVotes - $negativeVotes
            ];
        }
    
        $search = $request->query->get('search');
        if ($search) {
            $recipes = $repository->findSearch($search);
            $categories = $cr->findAll();
    
            // Recalculer la différence des votes pour les recettes filtrées
            $recipesWithVotes = [];
            foreach ($recipes as $recipe) {
                $endorsements = $er->findBy(['recipe_id' => $recipe]);
                $positiveVotes = 0;
                $negativeVotes = 0;
    
                foreach ($endorsements as $endorsement) {
                    if ($endorsement->isVote()) {
                        $positiveVotes++;
                    } else {
                        $negativeVotes++;
                    }
                }
    
                $recipesWithVotes[] = [
                    'recipe' => $recipe,
                    'voteDifference' => $positiveVotes - $negativeVotes
                ];
            }
    
            return $this->render('recipes/index.html.twig', ['recipesWithVotes' => $recipesWithVotes, 'categories' => $categories]);
        }
    
        return $this->render('recipes/index.html.twig', ['recipesWithVotes' => $recipesWithVotes, 'categories' => $categories]);
    }
    

    #[Route('{id}/favorites/new', name: 'favorites_new',methods: ['GET','POST'])]
    public function fav_new(Request $request, EntityManagerInterface $em, Recipes $recipe): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('new_fav', $submittedToken))
        {
            $fav = new Favorites;
            $fav->setDate(new \DateTime('now'));
            $fav->setUserId($this->getUser());
            $fav->setRecipeId($recipe);

            $em->persist($fav); // Prépare le favoris à être sauvegardée dans la base de données
            $em->flush();
        }
        return $this->redirectToRoute('recipe_show',['id'=> $recipe->getId()]);
    }

    #[Route('{id}/favorites/delete/{fav}', name: 'favorites_delete',methods: ['GET','POST'])]
    public function fav_delete($id,Favorites $fav,Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('del_fav', $submittedToken))
        {
            $em->remove($fav); // Supprime une recette de la base de données
            $em->flush(); // Sauvegarde la suppression dans la base de données
        }
        return $this->redirectToRoute('recipe_show',['id'=> $id]);
    }
}

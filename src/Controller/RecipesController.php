<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Flex\Recipe;
use App\Entity\Recipes;
use App\Entity\Favorites;
use App\Repository\RecipesRepository;
use Doctrine\ORM\EntityManager;
use App\Repository\CommentsRepository;
use App\Repository\CategoryRepository;
use App\Repository\FavoritesRepository;

class RecipesController extends AbstractController
{

    #[Route('/recipes', name: 'all_recipes',methods:'GET')]
    public function allRecipes(Request $request, RecipesRepository $repository): Response
    {
        $recipes = $repository->findAll();
        return $this->render('recipes/index.html.twig', ['recipes'=>$recipes]);
    }

    #[Route('/recipes/edit/{id}', name: 'edit_recipes',methods:['GET','POST'])]
    public function editRecipes(Recipes $recipe, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        // $recipe = new Recipes();

        if($request->isMethod('POST'))
        {
            $recipe->setTitle($request->request->get("_title")); // Attribue le titre depuis la requête
            $recipe->setIntroduction($request->request->get("_introduction")); // Attribue le contenu depuis la requête
            $recipe->setPortions($request->request->get("_portions"));

            $em->persist($recipe); // Prépare l'entité $recipe à être sauvegardée dans la base de données
            $em->flush(); // Sauvegarde réellement les données dans la base de données
            $this->addFlash('success',"La recette a bien été modifié");
            return $this->redirectToRoute('all_recipes');
        }
        return $this->render('recipes/edit.html.twig',['recipe'=>$recipe]);
    }
    #[Route('/recipes/new', name: 'new_recipe', methods: ['GET', 'POST'])]
    public function createRecipes(Request $request, EntityManagerInterface $em, CategoryRepository $cr): Response
    {

        $categories = $cr->findAll();
        $recipe = new Recipes();
        
        // if($form->isSubmitted() && $form->isValid()){
        if ($request->isMethod('POST')) { // Si la méthode de la requête est POST (c'est-à-dire que le formulaire a été soumis)
        
            // On récupère les données soumises dans le formulaire et on les attribue à l'entité
            $recipe->setTitle($request->request->get("_title")); // Attribue le titre depuis la requête
            $recipe->setImage($request->request->get("_image"));
            $recipe->setIntroduction($request->request->get("_introduction")); // Attribue le contenu depuis la requête
            $recipe->setPortions($request->request->get("_portions"));
            $recipe->setDate(new \DateTime('now'));

            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
            $user = $this->getUser();
            $recipe->setUserId($user);

            // Récupérer l'ID de la catégorie depuis le formulaire
            $categorieId = $request->request->get('id_categorie',[]);
        

            for ($i=0;!empty($categorieId[$i]); $i++)
            {
                // Rechercher l'entité Categorie correspondante
                $categorie = $cr->find($categorieId[$i]);
                
                // Attribue l'entité categorie a la variable recipe
                $recipe->addCategory($categorie);
            }
            
            $em->persist($recipe); // Prépare l'entité $recipe à être sauvegardée dans la base de données
            $em->flush(); // Sauvegarde réellement les données dans la base de données
            $this->addFlash('success',"La recette a bien été crée");
            return $this->redirectToRoute('new_recipe');
        }
        return $this->render('recipes/new.html.twig',['categories' => $categories]);
    }

    #[Route('/recipes/delete/{id}', name:  'recipe_delete', methods: ['POST','GET'])] // La route '/{id}/delete' permet de supprimer une recette
    public function delete(Recipes $recipe, EntityManagerInterface $em): Response // La méthode delete() permet de supprimer une recette existante
    {
        $em->remove($recipe); // Supprime une recette de la base de données
        $em->flush(); // Sauvegarde la suppression dans la base de données

        return $this->redirectToRoute('all_recipes'); // Redirige vers la liste des recettes après suppression
    }

    #[Route('/recipes/{id}', name: 'recipe_show', methods:'GET')]
    public function show(Recipes $recipe,CommentsRepository $commentaireRepository, FavoritesRepository $fr): Response
    {

        $recipeID = $recipe->getId(); // Récupérer l'ID de la catégorie depuis le formulaire
        $user = $this->getUser(); // Récupérer informations utilisateur connecté

        $commentaires = $commentaireRepository->findBy(['recipe_id'=>$recipeID]);// Rechercher les commentaires correspondants
        $fav = $fr->findOneBy(['userId'=>$user,'recipeId'=>$recipeID]);// Recherche du favoris en fonction de la recette et l'utilisateur

        return $this->render('recipes/show.html.twig',[
            'recipe' => $recipe,
            'commentaires' => $commentaires,
            'fav' => $fav
        ]);
    }
}
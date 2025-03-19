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
use App\Entity\Ingredient;
use App\Entity\Step;
use App\Repository\RecipesRepository;
use App\Repository\CommentsRepository;
use App\Repository\CategoryRepository;
use App\Repository\EndorsementRepository;
use App\Repository\FavoritesRepository;
use App\Repository\IngredientRepository;
use App\Repository\StepRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RecipesController extends AbstractController
{

    #[Route('/', name: 'all_recipes',methods:['GET', 'POST'])]
    public function allRecipes(Request $request, RecipesRepository $repository, CategoryRepository $cr, EndorsementRepository $er): Response
    {
        $recipes = $repository->findAll();
        $categories = $cr ->findAll();
    
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
        if($search)
        {
            $search = $request->query->get('search');
            $recipes = $repository->findSearch($search);
            $categories = $cr->findAll();
            
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
        return $this->render('recipes/index.html.twig', ['recipesWithVotes' => $recipesWithVotes,'categories'=>$categories]);
    }

    #[Route('/recipes/edit/{id}', name: 'edit_recipes',methods:['GET','POST'])]
    public function editRecipes(Recipes $recipe, Request $request, EntityManagerInterface $em, StepRepository $sr, ValidatorInterface $validator, IngredientRepository $ir): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('edit', $submittedToken))
        {
            $recipe->setTitle($request->request->get("_title")); // Attribue le titre depuis la requête
            $recipe->setIntroduction($request->request->get("_introduction")); // Attribue le contenu depuis la requête
            $recipe->setPortions($request->request->get("_portions"));

            $old_image = $recipe->getImage();
            
            $file = $request->files->get("image_");
            
            if ($file)
            {
                // ajout nouvelle image
                $fileName = uniqid().'.'.$file->guessExtension();
                $destination = $this->getParameter('upload_directory');

                $file->move($destination, $fileName);
                $recipe->setImage($fileName);

                // suppression ancienne image
                if($old_image)
                {
                    $old_filePath = $this->getParameter('upload_directory'). '/' .$old_image;
                    unlink($old_filePath);
                }
            }
            
            $steps = $request->request->get("_steps");
            $steps_id = $sr->findBy(['recipe_id'=> $recipe]);
            
            $i=0;
            foreach($steps_id as $value)
            {
                $steps_id[$i]->setContent($steps[$i]);
                $i++;
            }

            $ingres = $request->request->get("_ingres");
            $units = $request->request->get("_units");
            $quants = $request->request->get("_quantites");
            $ingres_id = $ir->findBy(['recipe_id'=> $recipe]);

            $k=0;
            foreach($ingres_id as $counter)
            {
                $ingres_id[$k]->setName($ingres[$k]);
                $ingres_id[$k]->setDose($quants[$k]);
                $ingres_id[$k]->setUnit($units[$k]);
                $k++;
            }

            $em->persist($recipe, $steps_id, $ingres_id); // Prépare l'entité $recipe et les étapes à être sauvegardée dans la base de données
            
            $errors = $validator->validate([$recipe,$steps_id]);
            if(count($errors) > 0)
            {
                $this->addFlash('error',"erreur d'informations formulaire");
                return $this->redirectToRoute('all_recipes');
            }

            $em->flush(); // Sauvegarde réellement les données dans la base de données
            $this->addFlash('success',"La recette a bien été modifié");
            return $this->redirectToRoute('all_recipes');
        }
        return $this->render('recipes/edit.html.twig',['recipe'=>$recipe,'steps'=>$recipe->getSteps(), 'ingredients'=>$recipe->getIngredients()]);
    }

    

    #[Route('/recipes/new', name: 'new_recipe', methods: ['GET', 'POST'])]
    public function createRecipes(Request $request, EntityManagerInterface $em, CategoryRepository $cr,ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $categories = $cr->findAll();
        $recipe = new Recipes();

        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('new', $submittedToken)) { // Si la méthode de la requête est POST (c'est-à-dire que le formulaire a été soumis)
            
            // On récupère les données soumises dans le formulaire et on les attribue à l'entité
            $recipe->setTitle($request->request->get("_title")); // Attribue le titre depuis la requête
            $recipe->setIntroduction($request->request->get("_introduction")); // Attribue le contenu depuis la requête
            $recipe->setPortions($request->request->get("_portions"));
            $recipe->setDate(new \DateTime('now'));
            
            $file = $request->files->get("image_");
            
            if ($file) {
                $fileName = uniqid().'.'.$file->guessExtension();
                $destination = $this->getParameter('upload_directory');

                $file->move($destination, $fileName);
                $recipe->setImage($fileName);
            }

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

            //création des étapes
            
            $array = $request->request->get('_steps',[]);

            $j = 1;
            for($i = 0; $i < 20 ; $i++)
            {
                if($array[$i] != null)
                {
                    $step = New Step();
                    $step -> setRecipeId($recipe);
                    $step -> setContent($array[$i]);
                    $step -> setRank($j);

                    $em->persist($step);
                    $j++;
                }
            }

            //Création des ingrédients

            $ingres = $request->request->get('_ingres',[]);
            $quantites =  $request->request->get('_quantites',[]);
            $units =  $request->request->get('_units',[]);

            for($i = 0; $i < 31 ; $i++)
            {
                if($ingres[$i] != null)
                {
                    $ingr = New Ingredient;
                    $ingr -> setName($ingres[$i]);
                    $ingr -> setUnit($units[$i]);
                    $ingr -> setDose($quantites[$i]);
                    $ingr -> setRecipeId($recipe);
                    
                    $em->persist($ingr);
                }
            }

            // Validation
            $errors = $validator->validate([$recipe,$step,$ingr]);
            if(count($errors) > 0)
            {
                $this->addFlash('error',"erreur d'informations formulaire");
                return $this->redirectToRoute('all_recipes');
            }

            $em->flush(); // Sauvegarde réellement les données dans la base de données

            $this->addFlash('success',"La recette a bien été crée");
            return $this->redirectToRoute('recipe_show',['id' => $recipe->getId()]);
        }
        return $this->render('recipes/new.html.twig',['categories' => $categories]);
    }
    

    #[Route('/recipes/delete/{id}', name:  'recipe_delete', methods: ['POST','GET'])] // La route '/{id}/delete' permet de supprimer une recette
    public function delete(Recipes $recipe, EntityManagerInterface $em, Request $request): Response // La méthode delete() permet de supprimer une recette existante
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('delete_rec', $submittedToken))
        {
            $em->remove($recipe); // Supprime une recette de la base de données
            $em->flush(); // Sauvegarde la suppression dans la base de données
        }
        return $this->redirectToRoute('all_recipes'); // Redirige vers la liste des recettes après suppression
    }

    #[Route('/recipes/{id}', name: 'recipe_show', methods:'GET')]
    public function show(Recipes $recipe,CommentsRepository $commentaireRepository, FavoritesRepository $fr,EndorsementRepository $er,StepRepository $sr): Response
    {
        $recipeID = $recipe->getId();
        $user = $this->getUser();
    
        $commentaires = $commentaireRepository->findBy(['recipe_id' => $recipeID]);
        $fav = $fr->findOneBy(['userId' => $user, 'recipeId' => $recipeID]);
        $allEndors = $er->findBy(['recipe_id' => $recipeID]);
        $endors = $er->findOneBy(['user_id' => $user, 'recipe_id' => $recipeID]);
        $steps = $sr->findBy(['recipe_id' => $recipe]);
    
        // Calculer le nombre de votes positifs et négatifs
        $positiveVotes = 0;
        $negativeVotes = 0;
    
        foreach ($allEndors as $endorsement) {
            if ($endorsement->isVote()) {
                $positiveVotes++;
            } else {
                $negativeVotes++;
            }
        }
    
        $voteDifference = $positiveVotes - $negativeVotes;
    
        return $this->render('recipes/show.html.twig', [
            'recipe' => $recipe,
            'commentaires' => $commentaires,
            'fav' => $fav,
            'endors' => $endors,
            'steps' => $steps,
            'voteDifference' => $voteDifference,
            'positiveVotes' => $positiveVotes,
            'negativeVotes' => $negativeVotes
        ]);
    }
    
    #[Route('/my_recipes', name: 'my_recipes', methods: ['GET', 'POST'])]
    public function myRecipes(Request $request, RecipesRepository $repository, CategoryRepository $cr, EndorsementRepository $er): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $recipes = $repository->findBy(['user_id' => $this->getUser()]);
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
}
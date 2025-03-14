<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Recipes;
use App\Entity\Comments;
use App\Repository\CommentsRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentsController extends AbstractController
{

    #[Route( '/recipes/{id}/commentaire/new',name: 'commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, Recipes $recipe, ValidatorInterface $validator): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('new', $submittedToken)){

            $commentaire = new Comments(); // On crée une nouvelle instance de l'entité commentaire

            $commentaire->setUserId($this->getUser()); // Attribue le titre depuis la requête

            $commentaire->setRecipeId($recipe);
            $commentaire->setContent($request->request->get('contenu')); // Attribue le contenu depuis la requête 
            $commentaire->setDate(new \DateTime('now'));// Attribue la date actuel 
            
            $em->persist($commentaire); // Prépare l'entité $commentaire à être sauvegardée dans la base de données

            $errors = $validator->validate([$commentaire]);

            if(count($errors) > 0)
            {
                $this->addFlash('error',"erreur d'informations formulaire");
                return $this->redirectToRoute('recipe_show', [
                    'id'=> $recipe->getId()
                ]);
            }

            $em->flush(); // Sauvegarde réellement les données dans la base de données
            
            return $this->redirectToRoute('recipe_show', [
                'id'=> $recipe->getId()
            ]);
        }
        
        return $this->render('comments/new.html.twig', [
            'recipe' => $recipe,
        ]);
    }



    #[Route('/recipes/{id}/edit/{com}', name: 'commentaire_edit', methods: ['GET', 'POST'])]
    public function edit($com,Request $request,CommentsRepository $commentaireRepository, EntityManagerInterface $em,Recipes $recipe, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $commentaire = $commentaireRepository->find($com);
        
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('edit', $submittedToken)){

            // On récupère les données soumises dans le formulaire et on les attribue à l'entité $commentaire
            $commentaire->setContent($request->request->get('contenu')); // Attribue le contunu depuis la requête        
            
            $em->persist($commentaire); // Prépare l'entité $commentaire à être sauvegardée dans la base de données

            $errors = $validator->validate([$commentaire]);
            if(count($errors) > 0)
            {
                $this->addFlash('error',"erreur d'informations formulaire");
                return $this->redirectToRoute('recipe_show', [
                    'id'=> $recipe->getId()
                ]);
            }
            
            $em->flush(); // Sauvegarde réellement les données dans la base de données
            
            return $this->redirectToRoute('recipe_show', [
                'id'=> $recipe->getId()
            ]);
        }

        return $this->render('comments/edit.html.twig', [
            'recipe' => $recipe,
            'commentaire' => $commentaire
        ]);
    }

    #[Route('/recipes/{recipe}/commentaire/delete/{id}',name: 'commentaire_delete', methods: ['POST'])]
    public function delete($recipe,Comments $commentaire , EntityManagerInterface $entityManager, Request $request): Response 
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $submittedToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete', $submittedToken))
        {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }
        return $this->redirectToRoute('recipe_show', [
            'id'=> $recipe
        ], Response::HTTP_SEE_OTHER);
    }

    // API

    #[Route('api/commentaires/{id}/delete',name: 'api_commentaire_delete', methods: ['DELETE'])]
    public function delete_api(Comments $commentaire , EntityManagerInterface $entityManager): Response 
    {       
            $entityManager->remove($commentaire);
            $entityManager->flush();   

            return new JsonResponse(['status' => 'User deleted'], JsonResponse::HTTP_OK);
    }

    //Afficher un commentaire et ses informations pour les modérateurs côté API
    #[Route('api/commentaires',name: 'commentaire_index', methods: ['GET'])]
    public function index(CommentsRepository $commentaireRepository): Response
    {
        $commentaires = $commentaireRepository->findAll();

        $data = array_map(function (Comments $commentaire) {
            return [
                'id' => $commentaire->getId(),
                'user_id' => $commentaire-> getUserId(),
                'recipe_id' => $commentaire->getRecipeId(),
                'contenu' => $commentaire->getContent(),
            ];
            }, $commentaires);
        return new JsonResponse($data, JsonResponse::HTTP_OK);

    }
}

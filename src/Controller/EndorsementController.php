<?php

namespace App\Controller;

use App\Entity\Endorsement;
use App\Entity\Recipes;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EndorsementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class EndorsementController extends AbstractController
{
    #[Route('/endorsement', name: 'app_endorsement')]
    public function index(): Response
    {
        return $this->render('endorsement/index.html.twig', [
            'controller_name' => 'EndorsementController',
        ]);
    }

    #[Route('/recipes/{id}/endorsement/new', name: 'endorsement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Recipes $recipe,EntityManagerInterface $em, EndorsementRepository $er): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        $dupes = $er->findOneBy(['user_id'=> $user,'recipe_id'=> $recipe]); //Cherche un doublon en base
        $recipeId = $recipe->getId();
        
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('new_endors', $submittedToken))
        {
            $endors = new Endorsement();
            $vote = $request->request->get("vote");

            $endors->setVote($vote);
            $endors->setDate(new \DateTime('now'));
            
            $endors->setUserId($user);
            $endors->setRecipeId($recipe);

            if($dupes)
            {
                $em->remove($dupes); // Supprime le doublon de la BDD
                $em->flush();
                return $this->redirectToRoute('recipe_show',['id'=>$recipeId]);
            }

            $em->persist($endors);
            $em->flush();
            return $this->redirectToRoute('recipe_show',['id'=>$recipeId]);
        }
        return $this->redirectToRoute('recipe_show',['id'=>$recipeId]);
    }
}

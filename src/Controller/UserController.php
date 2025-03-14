<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    public function allUser(UserRepository $repository) : JsonResponse
    {
        $users = $repository->findAll();
        
        $data = array_map(function(User $user)
        {
            return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            ];
        }, $users);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    public function show(User $user): JsonResponse
    {
        $data = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    // public function show(int $id, EntityManagerInterface $em): Jsonresponse
    // {
    //     $data = $em->getRepository(User::class)->find($id);

    //     return new JsonResponse($data, JsonResponse::HTTP_OK);
    // }

    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user -> setUsername($data['username']);
        $user -> setEmail($data['email']);
        
        $user -> setPassword($data['password']);
        // Hachage du mot de passe avant de le sauvegarder dans la base de données
        $hashedPassword = password_hash($request->request->get('password'), PASSWORD_BCRYPT); // Utilise bcrypt pour sécuriser le mot de passe
        $user->setPassword($hashedPassword); // On attribue le mot de passe haché à l'utilisateur

        $user -> setRoles($data['roles'] ?? ['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'User created'], JsonResponse::HTTP_CREATED);
    }

    

    public function update(Request $request, User $user, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if(isset($data['Username'])){
            $user->setUsername($data['Username']);
        }

        if(isset($data['email'])){
            $user->setEmail($data['email']);
        }

        if(isset($data['roles'])){
            $user->setRoles($data['roles']);
        }

        $em->flush();

        return new JsonResponse (['status'=>'User updated'], JsonResponse::HTTP_OK);
    }

    public function delete(User $user, EntityManagerInterface $em) : Jsonresponse
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(['status' => 'User deleted'],JsonResponse::HTTP_OK);
    }

    // non-api

    #[Route('/new', name: 'user_new_cla', methods: ['GET', 'POST'])] // La route '/new' pour afficher le formulaire de création et traiter l'envoi du formulaire
    public function new_cla(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response // La méthode new() gère l'affichage et la création de nouveaux utilisateurs
    {
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('new', $submittedToken)) { // Si la méthode de la requête est POST (c'est-à-dire que le formulaire a été soumis) et que le token est valide
            $user = new User(); // On crée une nouvelle instance de l'entité User

            // On récupère les données soumises dans le formulaire et on les attribue à l'entité $user
            $user->setUsername($request->request->get('name')); // Attribue le nom de l'utilisateur depuis la requête
            $user->setEmail($request->request->get('email')); // Attribue l'email depuis la requête

            // Hachage du mot de passe avant de le sauvegarder dans la base de données
            $hashedPassword = password_hash($request->request->get('password'), PASSWORD_BCRYPT); // Utilise bcrypt pour sécuriser le mot de passe
            $user->setPassword($hashedPassword); // On attribue le mot de passe haché à l'utilisateur

            $role = $request->request->get('role', 'ROLE_USER'); // On récupère le rôle du formulaire. Par défaut, il sera 'ROLE_USER'
            $user->setRoles([$role]); // Attribue le rôle à l'utilisateur

            $em->persist($user); // Prépare l'entité $user à être sauvegardée dans la base de données

            $errors = $validator->validate($user);
            if(count($errors) > 0)
            {
                $this->addFlash('error',"erreur d'informations formulaire");
                return $this->redirectToRoute('user_new_cla');
            }

            $em->flush(); // Sauvegarde réellement les données dans la base de données

            return $this->redirectToRoute('all_recipes'); // Redirige l'utilisateur vers la page principale
        }
        return $this->render('user/new.html.twig');
    }

    #[Route('/{id}/delete', name: 'user_delete_cla', methods: ['POST','GET'])] // La route '/{id}/delete' permet de supprimer un utilisateur
    public function delete_cla(User $user, EntityManagerInterface $em,Request $request): Response // La méthode delete() permet de supprimer un utilisateur existant
    {
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('delete', $submittedToken))
        {
            $user = $this->getUser();
            $em->remove($user); // Supprime l'utilisateur de la base de données
            $em->flush(); // Sauvegarde la suppression dans la base de données
        }
        return $this->redirectToRoute('all_recipes'); // Redirige vers la liste des utilisateurs après suppression
    }

    #[Route('/{id}/edit', name: 'user_edit_cla', methods: ['GET', 'POST'])] // La route '/{id}/edit' permet de modifier un utilisateur existant
    public function edit_cla(User $user, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response // La méthode edit() permet de modifier les informations d'un utilisateur existant
    {
        $submittedToken = $request->request->get('_token');

        if($request->isMethod('POST') && $this->isCsrfTokenValid('edit', $submittedToken)) { // Si la requête est de type POST (formulaire soumis)
            // Récupère et met à jour les informations de l'utilisateur
            $user->setUsername($request->request->get('name')); // Modifie le nom de l'utilisateur
            $user->setEmail($request->request->get('email')); // Modifie l'email de l'utilisateur

            $role = $request->request->get('role', 'ROLE_USER'); // Récupère et met à jour le rôle de l'utilisateur
            $user->setRoles([$role]); // Modifie le rôle de l'utilisateur

            $em->persist($user);

            $errors = $validator->validate($user);
            if(count($errors) > 0)
            {
                $this->addFlash('error',"erreur d'informations formulaire");
                return $this->redirectToRoute('user/edit.html.twig', ['user' => $user]);
            }

            $em->flush(); // Sauvegarde les modifications apportées à l'utilisateur dans la base de données

            return $this->redirectToRoute('user_list'); // Redirige vers la page de la liste des utilisateurs après modification
        }
        return $this->render('user/edit.html.twig', ['user' => $user]);
    }

    #[Route('/{id}/parameters', name: 'parameters', methods:['GET'])]
    public function parameters():Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('settings.html.twig');
    }
}

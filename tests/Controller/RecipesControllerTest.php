<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Recipes;

class RecipesControllerTest extends WebTestCase
{
    public function testShowRecipe()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Create a test recipe
        $recipe = new Recipes();
        $recipe->setTitle('Test Recipe');
        $entityManager->persist($recipe);
        $entityManager->flush();

        $client->request(Request::METHOD_GET, '/recipes/' . $recipe->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Test Recipe');
    }

    public function testAllRecipes()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'All Recipes');
    }
}

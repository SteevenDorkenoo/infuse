<?php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CommentsControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/api/commentaires');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Vérifie le format json
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseContent);

        if (!empty($responseContent)) {
            $this->assertArrayHasKey('id', $responseContent[0]);
            $this->assertArrayHasKey('user_id', $responseContent[0]);
            $this->assertArrayHasKey('recipe_id', $responseContent[0]);
            $this->assertArrayHasKey('contenu', $responseContent[0]);
        }
    }
}

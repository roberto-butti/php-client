<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Storyblok\BaseClient;
use Storyblok\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

final class BaseClientTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->makeMockClient();
    }

    protected function makeMockClient($ssl = false): void
    {
        $content = file_get_contents("./tests/mock/stories.json");
        $mocks = [
            new Response(200, ['server' => 'nginx/1.18.0'], $content),
            new Response(202, ['Content-Length' => 0]),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ];
        $this->client = new Client('your-storyblok-private-token');

        $this->client->mockable($mocks);

    }

    public function testCanBeInstanced(): void
    {
        $this->assertInstanceOf(
            BaseClient::class,
            new BaseClient()
        );
    }
    public function testSetApiKey(): void
    {
        $b = new BaseClient("apikey");
        $this->assertEquals(
            "apikey",
            $b->getApiKey()
        );
        $b->setApiKey("apikey2");
        $this->assertEquals(
            "apikey2",
            $b->getApiKey()
        );
    }

    public function testSetCache(): void
    {
        $b = new BaseClient("apikey");
        $b->setMaxRetries(3);
        $this->assertEquals(
            null,
            $b->getTimeout()
        );
    }
    public function testCallApi(): void
    {
        $storyResponse = $this->client->getStories();
        $story= $storyResponse->getBody();
        $this->assertEquals(
            200,
            $this->client->getCode()
        );
        $h = $storyResponse->getHeaders();
        $this->assertIsArray($h);
        $this->assertCount(1, $h);
        $this->assertArrayHasKey("server", $h);

        $this->assertIsArray($story);
        $this->assertCount(1, $story);
        $this->assertArrayHasKey("story", $story);
    }
    public function testProxy(): void
    {
        $proxy = "http://10.0.0.1";
        $storyResponse = $this->client->setProxy($proxy)->getStories();
        $this->assertEquals(
            $proxy,
            $this->client->getProxy()
        );
    }

    public function testTimeout(): void
    {
        $timeout = 1;
        $storyResponse = $this->client->setTimeout($timeout)->getStories();
        $this->assertEquals(
            $timeout,
            $this->client->getTimeout()
        );
    }

    public function testGenerateEndpoint(): void
    {
        $timeout = 1;
        $storyResponse = $this->client->setTimeout($timeout)->getStories();
        $this->assertEquals(
            $timeout,
            $this->client->getTimeout()
        );
    }
}

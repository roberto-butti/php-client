<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Storyblok\BaseClient;
use Storyblok\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

final class ClientRetryTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $content = file_get_contents("./tests/mock/stories.json");
        $mocks = [
            new Response(503, ['server' => 'nginx/1.18.0'], ""),
            new Response(503, ['server' => 'nginx/1.18.0'], ""),
            new Response(503, ['server' => 'nginx/1.18.0'], ""),
            new Response(200, ['server' => 'nginx/1.18.0'], ""),
        ];
        $this->client = new Client('your-storyblok-private-token');
        $this->client->mockable($mocks);
    }

    public function testRetry(): void
    {
        $storyResponse = $this->client->getStories();

        $story= $storyResponse->getBody();
        $this->assertEquals(
            200,
            $this->client->getCode()
        );
    }
}

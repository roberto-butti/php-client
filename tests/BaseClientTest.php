<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Storyblok\BaseClient;

final class BaseClientTest extends TestCase
{
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
}

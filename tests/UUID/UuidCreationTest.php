<?php


namespace UUID;


use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class UuidCreationTest extends TestCase
{
    /**
     * @test
     */
    public function should_generate_a_random_UUID()
    {
        $uuid = Uuid::uuid4()->toString();

        self::assertThat($uuid, self::isType('string'));
        self::assertThat($uuid, self::matchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/'));
        self::assertThat(strlen($uuid), self::equalTo(36));
    }
}
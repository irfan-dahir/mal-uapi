<?php

use Jikan\Helper\Episodes;
use Jikan\Jikan;
use PHPUnit\Framework\TestCase;

/**
 * Class JikanTest
 */
class JikanTest extends TestCase
{
    /**
     * @var Jikan
     */
    private $jikan;

    public function setUp()
    {
        $this->jikan = new Jikan();
    }

    /**
     * @test
     * @vcr JikanTest_it_gets_anime.yaml
     */
    public function it_gets_anime()
    {
        self::markTestSkipped('broken?');
        $this->jikan->Anime(
            (new \Jikan\Request\Anime(ANIME))->setID(21)
        );
        self::assertNotNull($this->jikan->response);
    }
}
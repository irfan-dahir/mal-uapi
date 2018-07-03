<?php

namespace JikanTest\Parser\Person;

use Goutte\Client;
use Jikan\Model\AnimeCard;
use Jikan\Model\MalUrl;
use Jikan\Parser\Genre\AnimeGenreParser;
use PHPUnit\Framework\TestCase;

/**
 * Class AnimeGenreParserTest
 */
class AnimeGenreParserTest extends TestCase
{
    /**
     * @var AnimeGenreParser
     */
    private $parser;

    public function setUp()
    {
        $client = new Client();
        $crawler = $client->request('GET', 'https://myanimelist.net/anime/genre/1');
        $this->parser = new AnimeGenreParser($crawler);
    }

    /**
     * @test
     * @vcr AnimeGenreParserTest.yaml
     */
    public function it_gets_url()
    {
        $url = $this->parser->getUrl();
        self::assertInstanceOf(MalUrl::class, $url);
    }

    /**
     * @test
     * @vcr AnimeGenreParserTest.yaml
     */
    public function it_gets_anime()
    {
        $anime = $this->parser->getGenreAnime();
        self::assertContainsOnlyInstancesOf(AnimeCard::class, $anime);
    }
}
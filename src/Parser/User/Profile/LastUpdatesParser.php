<?php

namespace Jikan\Parser\User\Profile;

use Jikan\Model\User\BaseLastUpdate;
use Jikan\Model\User\LastAnimeUpdate;
use Jikan\Model\User\LastMangaUpdate;
use Jikan\Model\User\LastUpdates;
use Symfony\Component\DomCrawler\Crawler;
use Jikan\Helper\Parser;

class LastUpdatesParser
{
    /**
     * @var Crawler
     */
    private Crawler $crawler;

    /**
     * LastUpdates constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * @return LastUpdates
     */
    public function getModel(): LastUpdates
    {
        return LastUpdates::fromParser($this);
    }

    /**
     * @return array
     *
     */
    public function getLastAnimeUpdates(): array
    {
        $arr = $this->parseBaseListUpdates('anime');
        $results = [];
        /**
         * @var $baseLastUpdate BaseLastUpdate
         */
        foreach ($arr as $baseLastUpdate) {
            $results[] = new LastAnimeUpdate($baseLastUpdate);
        }
        return $results;
    }

    /**
     * @return array
     *
     */
    public function getLastMangaUpdates(): array
    {
        $arr = $this->parseBaseListUpdates('manga');
        $results = [];
        /**
         * @var $baseLastUpdate BaseLastUpdate
         */
        foreach ($arr as $baseLastUpdate) {
            $results[] = new LastMangaUpdate($baseLastUpdate);
        }
        return $results;
    }

    private function parseBaseListUpdates(string $updateType): array
    {
        return $this->crawler->filterXPath("//div[contains(@class, 'updates {$updateType}')]/div")
            ->each(function (Crawler $crawler) {
                $a = $crawler->filterXPath('//a')->first();
                $img = $a->filterXPath('//img');
                $title = $img->attr('alt');
                $url = $a->attr('href');
                $imageUrl = $img->attr('data-src');
                $date = Parser::parseDate($crawler->filterXPath('//div/div[1]/span')->text());
                $progressScoreDiv = $crawler->filterXPath('//div/div[2]');
                $text = $progressScoreDiv->text();
                $scoreUnparsed = trim(substr($text, strpos($text, 'Scored') + strlen('Scored')));
                $score = ctype_digit($scoreUnparsed) ? intval($scoreUnparsed) : null;
                $progressTypeValueUnparsed = explode('·', $text)[0];
                /** @var  $total int|null */
                $total = null;
                /** @var  $progressed int|null */
                $progressed = null;

                $progressedTotalSeparatorIndex = strpos($progressTypeValueUnparsed, '/');
                if ($progressedTotalSeparatorIndex != false) {
                    $totalUnparsed = trim(substr($progressTypeValueUnparsed, $progressedTotalSeparatorIndex + 1));
                    $total = ctype_digit($totalUnparsed) ? intval($totalUnparsed) : null;
                    $lastSpace = strpos($progressTypeValueUnparsed, " ", -0);
                    $progressedUnparsed = trim(substr($progressTypeValueUnparsed, $lastSpace, $progressedTotalSeparatorIndex - $lastSpace));
                    $progressed = ctype_digit($progressedUnparsed) ? intval($progressedUnparsed) : null;
                    $status = trim(substr($progressTypeValueUnparsed, 0, $progressedTotalSeparatorIndex - strlen($progressedUnparsed)));
                    return new BaseLastUpdate($url, $title, $imageUrl, $progressed, $total, $status, $score, $date);
                }
                $progressTypeValueUnparsed = str_replace(" -", "", $progressTypeValueUnparsed);
                $status = trim($progressTypeValueUnparsed);
                return new BaseLastUpdate($url, $title, $imageUrl, $progressed, $total, $status, $score, $date);
            });
    }
}
<?php

namespace Jikan\Helper;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Parser
 *
 * @package Jikan\Helper
 */
class Parser
{
    /**
     *
     */
    private const ALLOWED_NODES = ['p', 'i', 'b', 'br', 'strong', 'u'];

    /**
     * Removes all html elements so the text is left over
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     * @throws \InvalidArgumentException
     */
    public static function removeChildNodes(Crawler $crawler): Crawler
    {
        if (!$crawler->count()) {
            return $crawler;
        }
        $crawler->children()->each(
            function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                if ($node === null || $node->nodeType === 3 || \in_array($node->nodeName, self::ALLOWED_NODES, true)) {
                    return;
                }
                $node->parentNode->removeChild($node);
            }
        );

        return $crawler;
    }

    /**
     * Extract the id from a mal url
     *
     * @param string $url
     *
     * @return int
     */
    public static function idFromUrl(string $url): int
    {
        return (int)preg_replace('#https://myanimelist.net(/\w+/)(\d+).*#', '$2', $url);
    }

    /**
     * Extract club ID from MAL URl
     *
     * @param string $url
     * @return int
     */
    public static function clubIdFromUrl(string $url) : int
    {
        return (int) preg_replace('~.*\.php\?cid=([\d]+)$~', '$1', $url);
    }

    /**
     * Extract the last property id from a mal url (e.g episode ID)
     *
     * @param string $url
     *
     * @return int
     */
    public static function suffixIdFromUrl(string $url): int
    {
        return (int) preg_replace('#https://myanimelist.net/.*/(\d+)#', '$1', $url);
    }

    /**
     * @param string $date
     *
     * @return \DateTimeImmutable|null
     */
    public static function parseForumDate(string $date): ?\DateTimeImmutable
    {
        if (!preg_match('/\d{4}/', $date)) {
            $date .= ', '.date('Y');
        }

        return self::parseDate($date);
    }

    /**
     * @param string $date
     *
     * @return \DateTimeImmutable|null
     */
    public static function parseDate(string $date): ?\DateTimeImmutable
    {
        if (preg_match('/^\d{4}$/', $date)) {
            return \DateTimeImmutable::createFromFormat('!Y-m-d', $date.'-01-01', new \DateTimeZone('UTC'));
        }

        if (preg_match('/(\w{3}), (\d{4})/', $date, $matches)) {
            return \DateTimeImmutable::createFromFormat(
                '!M d, Y',
                "{$matches[1]} 01, {$matches[2]}",
                new \DateTimeZone('UTC')
            );
        }

        try {
            return new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string|null $date
     *
     * @return \DateTimeImmutable|null
     */
    public static function parseDateMDY(?string $date): ?\DateTimeImmutable
    {
        if ($date === '-' || $date === null) {
            return null;
        }

        $dateArray = explode('-', $date);

        if ($dateArray[0] === '??' && $dateArray[1] === '??' && $dateArray[2] === date('y')) {
            return null;
        }

        $date = str_replace('??', '01', $date);

        return \DateTimeImmutable::createFromFormat('!m-d-y', $date, new \DateTimeZone('UTC')) ?: null;
    }

    /**
     * @param string|null $date
     *
     * @return \DateTimeImmutable|null
     */
    public static function parseDateDMY(?string $date): ?\DateTimeImmutable
    {
        if ($date === '-' || $date === null) {
            return null;
        }

        $dateArray = explode('-', $date);

        if ($dateArray[0] === '??' && $dateArray[1] === '??' && $dateArray[2] === date('y')) {
            return null;
        }

        $date = str_replace('??', '01', $date);

        return \DateTimeImmutable::createFromFormat('!d-m-y', $date, new \DateTimeZone('UTC')) ?: null;
    }

    /**
     * @param string $date
     *
     * @return \DateTimeImmutable|null
     * @throws \Exception
     */
    public static function parseDateMDYReadable(string $date): ?\DateTimeImmutable
    {
        $date = str_replace('  ', ' ', $date);

        if (preg_match('~[a-zA-z]+ \d+, \d{4}~', $date)) {
            return new \DateTimeImmutable($date, new \DateTimeZone('UTC'));
        }

        return null;
    }

    /**
     * @param string $dateTime
     * @return \DateTimeImmutable|null
     */
    public static function parseDateTimePST(string $dateTime) : ?\DateTimeImmutable
    {
        try{
            $malTimeZone = new \DateTimeZone('America/Los_Angeles'); //
            $parsedDateTime = new \DateTimeImmutable($dateTime,$malTimeZone);
            return $parsedDateTime->setTimezone(new \DateTimeZone('UTC'));
        }
        catch (\Exception $e){
            return null;
        }
    }

    /**
     * @param Crawler $crawler
     *
     * @return null|string
     * @throws \InvalidArgumentException
     */
    public static function textOrNull(Crawler $crawler): ?string
    {
        if (!$crawler->count()) {
            return null;
        }

        return $crawler->text();
    }

    /**
     * @param string
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function parseImageQuality(string $imageUrl) : string
    {
        // adding `v` prefix returns a very small thumbnail, as opposed to adding `l`
        $imageUrl = str_replace(['v.jpg', 't.jpg', 'l.jpg'], '.jpg', $imageUrl);
        return preg_replace('~/r/\d+x\d+~', '', $imageUrl);
    }

    /**
     * @param string
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function parseImageThumbToHQ(string $imageUrl) : string
    {
        return str_replace(['thumbs/', '_thumb'], '', $imageUrl);
    }

    /**
     * @param string $duration
     * @return int|null
     */
    public static function parseDurationToSeconds(string $duration): ?int
    {
        preg_match('~([0-9]{2}):([0-9]{2}):([0-9]{2})~', $duration, $match);
        if (empty($match)) {
            return null;
        }

        $hours = $match[1] ?? 0;
        $minutes = $match[2] ?? 0;
        $seconds = $match[3] ?? 0;

        return ($hours*60*60) + ($minutes*60) + $seconds;
    }
}

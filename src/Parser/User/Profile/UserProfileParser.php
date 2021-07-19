<?php

namespace Jikan\Parser\User\Profile;

use Jikan\Helper\Parser;
use Jikan\Model;
use Jikan\Model\User\AnimeStats;
use Jikan\Model\User\MangaStats;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class UserProfileParser
 *
 * @package Jikan\Parser
 */
class UserProfileParser
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * PersonParser constructor.
     *
     * @param Crawler $crawler
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Return the model
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @throws \Exception
     */
    public function getModel(): Model\User\Profile
    {
        return Model\User\Profile::fromParser($this);
    }

    public function getUserId(): ?int
    {
        $node = $this->crawler->filterXPath("//a[contains(@class, 'header-right')]");
        if (!$node->count()) {
            return null;
        }

        preg_match('#id=(.*)#', $node->attr('href'), $id);

        if (!empty($id)) {
            return (int)$id[1];
        }

        return null;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getUsername(): string
    {
        return preg_replace('#.*/(.*)$#', '$1', $this->getProfileUrl());
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getProfileUrl(): string
    {
        return $this->crawler->filterXPath('//meta[@property="og:url"]')->attr('content');
    }

    /**
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function getImageUrl(): ?string
    {
        try {
            return $this->crawler->filterXPath('//div[contains(@class, "user-image")]/img')->attr('data-src');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return \DateTimeImmutable|null
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function getJoinDate(): ?\DateTimeImmutable
    {
        return Parser::parseDateMDYReadable(
            $this->crawler->filterXPath('//span[contains(text(), \'Joined\')]/following-sibling::span')->text()
        );
    }


    /**
     * @return \DateTimeImmutable|null
     * @throws \Exception
     */
    public function getLastOnline(): ?\DateTimeImmutable
    {
        return Parser::parseDateTimePST(
            $this->crawler->filterXPath('//span[contains(text(), \'Last Online\')]/following-sibling::span')
            ->text());
    }

    /**
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function getGender(): ?string
    {
        $gender = $this->crawler->filterXPath('//span[contains(text(), \'Gender\')]/following-sibling::span');
        if (!$gender->count()) {
            return null;
        }

        return $gender->text();
    }

    /**
     * @return \DateTimeImmutable|null
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function getBirthday(): ?\DateTimeImmutable
    {
        $node = $this->crawler->filterXPath('//span[contains(text(), \'Birthday\')]/following-sibling::span');
        if (!$node->count()) {
            return null;
        }

        return Parser::parseDateMDYReadable(
            $node->text()
        );
    }

    /**
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function getLocation(): ?string
    {
        $gender = $this->crawler->filterXPath('//span[contains(text(), \'Location\')]/following-sibling::span');
        if (!$gender->count()) {
            return null;
        }

        return $gender->text();
    }

    /**
     * @return \Jikan\Model\User\AnimeStats
     * @throws \InvalidArgumentException
     */
    public function getAnimeStats(): AnimeStats
    {
        $this->crawler
            ->filterXPath('//div[@class=\'stats anime\']');

        return (new AnimeStatsParser($this->crawler))->getModel();
    }

    /**
     * @return \Jikan\Model\User\MangaStats
     * @throws \InvalidArgumentException
     */
    public function getMangaStats(): MangaStats
    {
        $this->crawler
            ->filterXPath('//div[@class=\'stats anime\']');

        return (new MangaStatsParser($this->crawler))->getModel();
    }

    /**
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function getAbout(): ?string
    {
        $about = $this->crawler->filterXPath('//div[@class=\'profile-about-user js-truncate-inner\']/table/tr/td/div');


        if (!$about->count()) {
            return null;
        }

        return trim(
            $about->html()
        );
    }

    /**
     * @return \Jikan\Model\User\Favorites
     * @throws \InvalidArgumentException
     */
    public function getFavorites(): Model\User\Favorites
    {
        // $node = $this->crawler->filterXPath('//ul[@class=\'favorites-list anime\']/li')
        $node = $this->crawler->filterXPath('//div[contains(@class, \'user-favorites\')]');

        return (new FavoritesParser($node))->getModel();
    }

    /**
     * @return Model\User\LastUpdates
     */
    public function getUserLastUpdates(): Model\User\LastUpdates
    {
        return (new LastUpdatesParser($this->crawler))->getModel();
    }
}

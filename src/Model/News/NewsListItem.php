<?php

namespace Jikan\Model\News;

use Jikan\Parser\News\NewsListItemParser;

/**
 * Class AnimeParser
 *
 * @package Jikan\Model
 */
class NewsListItem
{
    /**
     * @var int
     */
    private $malId;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $title;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    /**
     * @var string
     */
    private $authorUsername;

    /**
     * @var string
     */
    private $authorUrl;

    /**
     * @var string
     */
    private $forumUrl;

    /**
     * @var string|null
     */
    private $imageUrl;

    /**
     * @var int
     */
    private $comments;

    /**
     * @var string
     */
    private $excerpt;

    /**
     * @param NewsListItemParser $parser
     *
     * @return NewsListItem
     * @throws \InvalidArgumentException
     */
    public static function fromParser(NewsListItemParser $parser): self
    {
        $instance = new self();
        $instance->malId = $parser->getMalId();
        $instance->url = $parser->getUrl();
        $instance->title = $parser->getTitle();
        $instance->date = $parser->getDate();
        $instance->authorUsername = $parser->getAuthor()->getName();
        $instance->authorUrl = $parser->getAuthor()->getUrl();
        $instance->forumUrl = $parser->getDiscussionLink();
        $instance->imageUrl = $parser->getImage();
        $instance->comments = $parser->getComments();
        $instance->excerpt = $parser->getIntro();

        return $instance;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getAuthorUsername(): string
    {
        return $this->authorUsername;
    }

    /**
     * @return string
     */
    public function getAuthorUrl(): string
    {
        return $this->authorUrl;
    }

    /**
     * @return string
     */
    public function getForumUrl(): string
    {
        return $this->forumUrl;
    }

    /**
     * @return string|null
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @return int
     */
    public function getComments(): int
    {
        return $this->comments;
    }

    /**
     * @return string
     */
    public function getExcerpt(): string
    {
        return $this->excerpt;
    }
}

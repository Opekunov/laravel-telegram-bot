<?php


namespace Opekunov\LaravelTelegramBot;


class TelegramInlineQuery extends TelegramCore
{
    protected $payload = [
        'inline_query_id' => null,
        'results' => array(),
    ];

    public static function createAnswer(string $answerId): self
    {
        return new self($answerId);
    }

    public function __construct(string $answerId)
    {
        //$this->addAnswer($content);
        $this->payload['inline_query_id'] = $answerId;
        parent::__construct();
    }

    public function addAnswer(array $content): self
    {
        $this->payload['results'][] = $content;
        return $this;
    }

    public function addArticleAnswer(string $title, string $description, string $id, string $inputMessageText, string $thumbLink = null): self
    {
        $content = [
            'type' => 'article',
            "title" => $title,
            "description" => $description,
            "id" => $id,
            "input_message_content" => [
                "message_text" => $inputMessageText
            ]
        ];
        if ($thumbLink) $content['thumb_url'] = $thumbLink;

        return $this->addAnswer($content);
    }

    public function setPersonalMode(): self
    {
        $this->payload['is_personal'] = true;
        return $this;
    }

    public function returnAnswer()
    {
        return $this->sendRequest('answerInlineQuery', $this->payload);
    }

    public function setCacheZero(): self
    {
        $this->payload['cache_time'] = 0;
        return $this;
    }

}

<?php

namespace Opekunov\LaravelTelegramBot;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Opekunov\LaravelTelegramBot\Exceptions\InvalidBotTokenException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramBotKickedException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramConnectionRefusedException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramRequestException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramTooManyRequestsException;
use Throwable;

class TelegramExceptionHandler
{
    public TelegramTooManyRequestsException|InvalidBotTokenException|TelegramBotKickedException|TelegramRequestException|TelegramConnectionRefusedException $exception;

    /**
     * @param  Throwable  $exception
     * @param  int|null  $chatId
     *
     */
    public function __construct(Throwable $exception, ?int $chatId = null)
    {
        if ($exception instanceof RequestException) {
            $statusCode = $exception->getResponse()->getStatusCode();
            $body = $exception->getResponse()?->getBody();
            return match ($statusCode) {
                404 => new InvalidBotTokenException(),
                429 => new TelegramTooManyRequestsException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious()),
                403 => new TelegramBotKickedException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious()),
                default => new TelegramRequestException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious()),
            };
        } elseif ($exception instanceof ConnectException) {
            return new TelegramConnectionRefusedException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        } else {
            return new TelegramRequestException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    /**
     * @throws TelegramConnectionRefusedException
     * @throws InvalidBotTokenException
     * @throws TelegramBotKickedException
     * @throws TelegramTooManyRequestsException
     * @throws TelegramRequestException
     */
    public function throw()
    {
        throw $this->exception;
    }
}

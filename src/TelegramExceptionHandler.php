<?php

namespace Opekunov\LaravelTelegramBot;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramBadTokenException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramBotKickedException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramConnectionRefusedException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramRequestException;
use Opekunov\LaravelTelegramBot\Exceptions\TelegramTooManyRequestsException;
use Throwable;

class TelegramExceptionHandler
{
    /**
     * @param  Throwable  $exception
     * @param  int|null  $chatId
     *
     * @return mixed
     * @throws TelegramBadTokenException
     * @throws TelegramBotKickedException
     * @throws TelegramConnectionRefusedException
     * @throws TelegramRequestException
     * @throws TelegramTooManyRequestsException
     */
    public function handle(Throwable $exception, ?int $chatId = null)
    {
        if ($exception instanceof RequestException) {
            $statusCode = $exception->getResponse()->getStatusCode();
            switch ($statusCode) {
                case 404:
                    throw new TelegramBadTokenException(
                        $chatId,
                        $exception->getCode(),
                        'Bad token. Response body: '.$exception->getResponse()->getBody()
                    );
                case 429:
                    throw new TelegramTooManyRequestsException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
                case 403:
                    throw new TelegramBotKickedException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
                default:
                    throw new TelegramRequestException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
            }
        } elseif ($exception instanceof ConnectException) {
            throw new TelegramConnectionRefusedException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        } else {
            throw new TelegramRequestException($chatId, $exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }
}

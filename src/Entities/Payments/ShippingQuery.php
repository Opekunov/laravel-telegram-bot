<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opekunov\LaravelTelegramBot\Entities\Payments;

use Opekunov\LaravelTelegramBot\Entities\Entity;
use Opekunov\LaravelTelegramBot\Entities\ServerResponse;
use Opekunov\LaravelTelegramBot\Entities\User;
use Longman\TelegramBot\Request;

/**
 * Class ShippingQuery
 *
 * This object contains information about an incoming shipping query.
 *
 * @link https://core.telegram.org/bots/api#shippingquery
 *
 * @method string          getId()              Unique query identifier
 * @method User            getFrom()            User who sent the query
 * @method string          getInvoicePayload()  Bot specified invoice payload
 * @method ShippingAddress getShippingAddress() User specified shipping address
 **/
class ShippingQuery extends Entity
{
    /**
     * {@inheritdoc}
     */
    protected function subEntities(): array
    {
        return [
            'from'             => User::class,
            'shipping_address' => ShippingAddress::class,
        ];
    }

    /**
     * Answer this shipping query.
     *
     * @param bool  $ok
     * @param array $data
     *
     * @return ServerResponse
     */
    public function answer(bool $ok, array $data = []): ServerResponse
    {
        return Request::answerShippingQuery(array_merge([
            'shipping_query_id' => $this->getId(),
            'ok'                => $ok,
        ], $data));
    }
}

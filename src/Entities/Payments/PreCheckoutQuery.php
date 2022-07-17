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
 * Class PreCheckoutQuery
 *
 * This object contains information about an incoming pre-checkout query.
 *
 * @link https://core.telegram.org/bots/api#precheckoutquery
 *
 * @method string    getId()               Unique query identifier
 * @method User      getFrom()             User who sent the query
 * @method string    getCurrency()         Three-letter ISO 4217 currency code
 * @method int       getTotalAmount()      Total price in the smallest units of the currency (integer, not float/double).
 * @method string    getInvoicePayload()   Bot specified invoice payload
 * @method string    getShippingOptionId() Optional. Identifier of the shipping option chosen by the user
 * @method OrderInfo getOrderInfo()        Optional. Order info provided by the user
 **/
class PreCheckoutQuery extends Entity
{
    /**
     * {@inheritdoc}
     */
    protected function subEntities(): array
    {
        return [
            'from'       => User::class,
            'order_info' => OrderInfo::class,
        ];
    }

    /**
     * Answer this pre-checkout query.
     *
     * @param bool  $ok
     * @param array $data
     *
     * @return ServerResponse
     */
    public function answer(bool $ok, array $data = []): ServerResponse
    {
        return Request::answerPreCheckoutQuery(array_merge([
            'pre_checkout_query_id' => $this->getId(),
            'ok'                    => $ok,
        ], $data));
    }
}

<?php
/*
 * Copyright.
 * (c) Aleksander Opekunov <iam@opekunov.com>
 */

namespace Opekunov\LaravelTelegramBot\Receivers;

/**
 * @method UpdateReceiver setUpMessageAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the  update type is Message. First argument is Message class, second argument is Handler class
 * @method UpdateReceiver setUpEditedMessageAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is EditedMessage. First argument is EditedMessage class, second argument is Handler class
 * @method UpdateReceiver setUpChannelPostAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is ChannelPost. First argument is ChannelPost class, second argument is Handler class
 * @method UpdateReceiver setUpEditedChannelPostAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is EditedChannelPost. First argument is EditedChannel class, second argument is Handler class
 * @method UpdateReceiver setUpInlineQueryAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is InlineQuery. First argument is InlineQuery class, second argument is Handler class
 * @method UpdateReceiver setUpChosenInlineResultAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is ChosenInlineResult. First argument is ChosenInlineResult class, second argument is Handler class
 * @method UpdateReceiver setUpCallbackQueryAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is CallbackQuery. First argument is CallbackQuery class, second argument is Handler class
 * @method UpdateReceiver setUpShippingQueryAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if  the update type is ShippingQuery. First argument is ShippingQuery class, second argument is Handler class
 * @method UpdateReceiver setUpPreCheckoutQueryAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is PreCheckout. First argument is PreCheckout class, second argument is Handler class
 * @method UpdateReceiver setUpPollAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Pool. First argument is Pool class, second argument is Handler class
 * @method UpdateReceiver setUpPollAnswerAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is PoolAnswer. First argument is PoolAnswer class, second argument is Handler class
 * @method UpdateReceiver setUpMyChatMemberAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is MyChatMember. First argument is MyChatMember class, second argument is Handler class
 * @method UpdateReceiver setUpChatMemberAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is ChatMember. First argument is ChatMember class, second argument is Handler class
 * @method UpdateReceiver setUpChatJoinRequestAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is ChatJoinRequest. First argument is ChatJoinRequest class, second argument is Handler class
 * @method UpdateReceiver setUpPreExecuteAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called before execute(). First argument is Handler class
 */
class UpdateReceiver extends Receiver
{
    protected array $actions = [
        'message'              => null,
        'edited_message'       => null,
        'channel_post'         => null,
        'edited_channel_post'  => null,
        'inline_query'         => null,
        'chosen_inline_result' => null,
        'callback_query'       => null,
        'shipping_query'       => null,
        'pre_checkout_query'   => null,
        'poll'                 => null,
        'poll_answer'          => null,
        'my_chat_member'       => null,
        'chat_member'          => null,
        'chat_join_request'    => null,
        'pre_execute'          => null
    ];

    /**
     * Run receiver actions
     *
     * @return void
     */
    public function execute(): void
    {
        if(!$this->checkFilters()) {
            return;
        }

        $handler = $this->handler;

        $this->preExecute($handler);

        $updateType = $handler->getUpdate()->getUpdateType();
        $this->executeByType($updateType, $handler->getUpdate()->getUpdateContent(), $handler);
    }

}

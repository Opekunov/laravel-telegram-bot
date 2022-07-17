<?php
/*
 * Copyright.
 * (c) Aleksander Opekunov <iam@opekunov.com>
 */

namespace Opekunov\LaravelTelegramBot\Receivers;

use Opekunov\LaravelTelegramBot\Entities\Message;
use Opekunov\LaravelTelegramBot\Handler;

/**
 * @method MessageReceiver setUpTextAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Text. First argument is Text class, second argument is Handler class
 * @method MessageReceiver setUpAudioAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Audio. First argument is Audio class, second argument is Handler class
 * @method MessageReceiver setUpAnimationAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Animation. First argument is Animation class, second argument is Handler class
 * @method MessageReceiver setUpDocumentAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Document. First argument is Document class, second argument is Handler class
 * @method MessageReceiver setUpGameAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Game. First argument is Game class, second argument is Handler class
 * @method MessageReceiver setUpPhotoAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Photo. First argument is Photo class, second argument is Handler class
 * @method MessageReceiver setUpStickerAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Sticker. First argument is Sticker class, second argument is Handler class
 * @method MessageReceiver setUpVideoAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Video. First argument is Video class, second argument is Handler class
 * @method MessageReceiver setUpVoiceAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Voice. First argument is Voice class, second argument is Handler class
 * @method MessageReceiver setUpVideoNoteAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is VideoNote. First argument is VideoNote class, second argument is Handler class
 * @method MessageReceiver setUpContactAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Contact. First argument is Contact class, second argument is Handler class
 * @method MessageReceiver setUpLocationAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Location. First argument is Location class, second argument is Handler class
 * @method MessageReceiver setUpVenueAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Venue. First argument is Venue class, second argument is Handler class
 * @method MessageReceiver setUpPollAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Poll. First argument is Poll class, second argument is Handler class
 * @method MessageReceiver setUpNewChatMembersAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is NewChatMembers. First argument is NewChatMembers class, second argument is Handler class
 * @method MessageReceiver setUpLeftChatMemberAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is LeftChatMember. First argument is LeftChatMember class, second argument is Handler class
 * @method MessageReceiver setUpNewChatTitleAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is NewChatTitle. First argument is NewChatTitle class, second argument is Handler class
 * @method MessageReceiver setUpNewChatPhotoAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is NewChatPhoto. First argument is NewChatPhoto class, second argument is Handler class
 * @method MessageReceiver setUpDeleteChatPhotoAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is DeleteChatPhoto. First argument is DeleteChatPhoto class, second argument is Handler class
 * @method MessageReceiver setUpGroupChatCreatedAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is GroupChatCreated. First argument is GroupChatCreated class, second argument is Handler class
 * @method MessageReceiver setUpSupergroupChatCreatedAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is SupergroupChatCreated. First argument is SupergroupChatCreated class, second argument is Handler class
 * @method MessageReceiver setUpChannelChatCreatedAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is ChannelChatCreated. First argument is ChannelChatCreated class, second argument is Handler class
 * @method MessageReceiver setUpMessageAutoDeleteTimerChangedAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is MessageAutoDeleteTimerChanged. First argument is MessageAutoDeleteTimerChanged class, second argument is Handler class
 * @method MessageReceiver setUpMigrateToChatIdAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is MigrateToChatId. First argument is MigrateToChatId class, second argument is Handler class
 * @method MessageReceiver setUpMigrateFromChatIdAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is MigrateFromChatId. First argument is MigrateFromChatId class, second argument is Handler class
 * @method MessageReceiver setUpPinnedMessageAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is PinnedMessage. First argument is PinnedMessage class, second argument is Handler class
 * @method MessageReceiver setUpInvoiceAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is Invoice. First argument is Invoice class, second argument is Handler class
 * @method MessageReceiver setUpSuccessfulPaymentAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is SuccessfulPayment. First argument is SuccessfulPayment class, second argument is Handler class
 * @method MessageReceiver setUpPassportDataAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is PassportData. First argument is PassportData class, second argument is Handler class
 * @method MessageReceiver setUpProximityAlertTriggeredAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is ProximityAlertTriggered. First argument is ProximityAlertTriggered class, second argument is Handler class
 * @method MessageReceiver setUpVideoChatScheduledAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is VideoChatScheduled. First argument is VideoChatScheduled class, second argument is Handler class
 * @method MessageReceiver setUpVideoChatStartedAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is VideoChatStarted. First argument is VideoChatStarted class, second argument is Handler class
 * @method MessageReceiver setUpVideoChatEndedAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is VideoChatEnded. First argument is VideoChatEnded class, second argument is Handler class
 * @method MessageReceiver setUpVideoChatParticipantsInvitedAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is VideoChatParticipantsInvited. First argument is VideoChatParticipantsInvited class, second argument is Handler class
 * @method MessageReceiver setUpWebAppDataAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is WebAppData. First argument is WebAppData class, second argument is Handler class
 * @method MessageReceiver setUpReplyMarkupAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called if the update type is ReplyMarkup. First argument is ReplyMarkup class, second argument is Handler class
 * @method MessageReceiver setUpPreExecuteAction(string|callable $func) Sets the function or Action::class (with __invoke()) that will be called before execute(). First argument is Message class, second argument is Handler class
 */
class MessageReceiver extends Receiver
{
    protected array $actions = [
        'text',
        'audio',
        'animation',
        'document',
        'game',
        'photo',
        'sticker',
        'video',
        'voice',
        'video_note',
        'contact',
        'location',
        'venue',
        'poll',
        'new_chat_members',
        'left_chat_member',
        'new_chat_title',
        'new_chat_photo',
        'delete_chat_photo',
        'group_chat_created',
        'supergroup_chat_created',
        'channel_chat_created',
        'message_auto_delete_timer_changed',
        'migrate_to_chat_id',
        'migrate_from_chat_id',
        'pinned_message',
        'invoice',
        'successful_payment',
        'passport_data',
        'proximity_alert_triggered',
        'video_chat_scheduled',
        'video_chat_started',
        'video_chat_ended',
        'video_chat_participants_invited',
        'web_app_data',
        'reply_markup',
        'pre_execute'
    ];

    protected Message $message;

    public function __construct(Message $message, Handler $handler)
    {
        $this->message = $message;
        parent::__construct($handler);
    }

    public function execute(): void
    {
        parent::execute();

        $handler = $this->handler;
        $message = $this->message;

        if (isset($this->actions['pre_execute'])) {
            call_user_func($this->actions['pre_execute'], $message, $handler);
        }

        $messageType = $message->getType();

        if (isset($this->actions[$messageType])) {
            call_user_func($this->actions[$messageType], $message, $handler);
        }
    }
}

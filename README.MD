# IMPORTANT! NOT FOR USING. Project is under development  

```php artisan vendor:publish --provider="Opekunov\LaravelTelegramBot" --tag="config"```

# UpdateReceiver

Example:
```php
use \Opekunov\LaravelTelegramBot\Receivers\UpdateReceiver;
use \Opekunov\LaravelTelegramBot\Entities\Message;

$receiver = new UpdateReceiver($handler);
$receiver->setUpMessageAction(function (Message $message) { /* some code */ })

//or (call __invoke())

$receiver->setUpMessageAction('Some\\Callable\\Class')
```

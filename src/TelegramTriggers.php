<?php


namespace App\Telegram;


use Illuminate\Support\Str;

class TelegramTriggers
{

    /**
     * Находит триггеры
     * @param string $text Текст сообщения
     * @param bool $firstOnly Вернуть только первое вхождение. Возвращает INT (reason)
     * @return array|null|int Массив триггеров, NULL или INT (reason), если $firstOnly = true
     */
    public static function findTriggers(string $text, $firstOnly = true)
    {
        $triggers = [];
        //EMOJI TRIGGERS
        $emojiTriggers = config('telegram.triggers.emojies');
        $textTriggers = config('telegram.triggers.words');
        $encodedEmojiString = json_encode($text);
        $emojies = array_keys($emojiTriggers);

        foreach ($emojies as $emoji) {
            if (self::findStrInMessage(trim(json_encode($emoji), '"'), trim($encodedEmojiString, '"'))) {
                if ($firstOnly) return $emojiTriggers[$emoji];
                $triggers[] = $emojiTriggers[$emoji];
            }
        }

        //Чистка строки
        $inp = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $text);
        $words = explode(' ', $inp);
        foreach ($words as $word) {
            if (Str::length($word) < 1) continue;
            $key = array_search(Str::lower($word), array_keys($textTriggers));
            if ($key !== false) {
                if($firstOnly) return array_values($textTriggers)[$key];
                $triggers[] = array_values($textTriggers)[$key];
            }
        }
        return count($triggers) > 0 ? $triggers : null;
    }

    /**
     * Поиск строки в тексте сообщения
     * @param $needle
     * @param null $haystack
     * @return bool
     */
    public static function findStrInMessage($needle, $haystack)
    {
        return mb_stripos($haystack, $needle) !== false;
    }
}
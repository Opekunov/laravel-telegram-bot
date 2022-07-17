<?php
/*
 * Copyright.
 * (c) Aleksander Opekunov <iam@opekunov.com>
 */

namespace Opekunov\LaravelTelegramBot\Receivers;

use Opekunov\LaravelTelegramBot\Handler;

abstract class Receiver
{
    /** @var array Available actions */
    protected array $actions;

    protected array $filters = [];

    protected Handler $handler;

    public function execute(): void
    {
        if (!empty($this->filters)) {
            foreach ($this->filters as $filter) {
                if (!call_user_func($filter)) {
                    return;
                }
            }
        }
    }

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function __call(string $method, array $arguments)
    {
        if (mb_strpos($method, 'setUp') === 0) {
            $func = $arguments[0];
            if (!is_callable($func) && !class_exists($func)) {
                throw new \TypeError('The argument must be callable or classname. Or class not exist');
            }

            $method = str_replace(['setUp', 'Action'], '', $method);
            $type = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $method));
            if (!array_key_exists($type, $this->actions)) {
                throw new \TypeError("Action $type not supported");
            }

            $this->actions[$type] = is_string($func) ? new $type : $func;

            return $this;
        }

        return false;
    }

    /**
     * Adds a execute() filter. If any of the filters returns FALSE, it will stop any actions.
     *
     * @param  callable  $func
     *
     * @return $this
     */
    public function addFilter(callable $func): self
    {
        $this->filters[] = $func;
        return $this;
    }

    public function addPrivateOnlyFilter(): self
    {
        $this->addFilter(fn() => $this->handler->getChat()->isPrivateChat());
        return $this;
    }

    public function addChannelOnlyFilter(): self
    {
        $this->addFilter(fn() => $this->handler->getChat()->isChannel());
        return $this;
    }

    public function addGroupChatFilter(): self
    {
        $this->addFilter(fn() => $this->handler->getChat()->isGroupChat());
        return $this;
    }

    public function addSuperGroupChatFilter(): self
    {
        $this->addFilter(fn() => $this->handler->getChat()->isSuperGroup());
        return $this;
    }

    public function addNotPrivateChatFilter(): self
    {
        $this->addFilter(fn() => !$this->handler->getChat()->isPrivateChat());
        return $this;
    }
}

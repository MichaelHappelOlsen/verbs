<?php

namespace Thunk\Verbs;

use Thunk\Verbs\Lifecycle\EventStore;
use Thunk\Verbs\Lifecycle\StateRegistry;

abstract class State
{
    public int|string|null $id = null;

    public int|string|null $last_event_id = null;

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function __construct()
    {
        app(StateRegistry::class)->register($this);
    }

    public static function load($from): static
    {
        $key = is_object($from) && method_exists($from, 'getVerbsStateKey')
            ? $from->getVerbsStateKey()
            : $from;

        return static::loadByKey($key);
    }

    public static function loadByKey($from): static
    {
        return app(StateRegistry::class)->load($from, static::class);
    }

    public function storedEvents()
    {
        return app(EventStore::class)
            ->read(state: $this)
            ->collect();
    }

    public static function singleton(): static
    {
        // FIXME: don't use "0"
        return app(StateRegistry::class)->load(0, static::class);
    }
}

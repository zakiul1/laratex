<?php

namespace App\Support;

class HookManager
{
    protected array $actions = [];
    protected array $filters = [];

    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        $source = $this->detectPluginFromStack();

        $this->actions[$hook][$priority][] = [
            'callback' => $callback,
            'plugin' => $source,
        ];
    }

    public function doAction(string $hook, ...$args): void
    {
        if (!isset($this->actions[$hook]))
            return;

        foreach ($this->getCallbacks($this->actions[$hook]) as $callback) {
            if (is_callable($callback)) {
                call_user_func_array($callback, $args);
            }
        }
    }

    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $source = $this->detectPluginFromStack();

        $this->filters[$hook][$priority][] = [
            'callback' => $callback,
            'plugin' => $source,
        ];
    }

    public function applyFilters(string $hook, $value, ...$args)
    {
        if (!isset($this->filters[$hook]))
            return $value;

        foreach ($this->getCallbacks($this->filters[$hook]) as $callback) {
            if (is_callable($callback)) {
                $value = call_user_func_array($callback, array_merge([$value], $args));
            }
        }

        return $value;
    }

    /**
     * ✅ Fix: Only return the 'callback' keys, sorted by priority
     */
    protected function getCallbacks(array $hookCallbacks): array
    {
        ksort($hookCallbacks);

        $flattened = array_merge(...array_values($hookCallbacks));

        return array_map(function ($item) {
            return $item['callback'];
        }, $flattened);
    }

    public function allActions(): array
    {
        return $this->actions;
    }

    public function allFilters(): array
    {
        return $this->filters;
    }

    protected function detectPluginFromStack(): ?string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            if (!empty($trace['file']) && str_contains($trace['file'], base_path('plugins/'))) {
                return basename(dirname($trace['file']));
            }
        }

        return null;
    }

    public function actionsByPlugin(): array
    {
        return $this->groupHooksByPlugin($this->actions);
    }

    public function filtersByPlugin(): array
    {
        return $this->groupHooksByPlugin($this->filters);
    }

    protected function groupHooksByPlugin(array $hooks): array
    {
        $result = [];

        foreach ($hooks as $hook => $priorities) {
            foreach ($priorities as $priority => $callbacks) {
                foreach ($callbacks as $item) {
                    $plugin = $item['plugin'] ?? 'core';
                    $result[$plugin][] = [
                        'hook' => $hook,
                        'priority' => $priority,
                        'callback' => is_string($item['callback']) ? $item['callback'] : 'Closure',
                    ];
                }
            }
        }

        return $result;
    }
}
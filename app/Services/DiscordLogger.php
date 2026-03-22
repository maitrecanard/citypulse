<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for sending log messages to a Discord webhook.
 *
 * Usage: app(DiscordLogger::class)->info('Message', ['key' => 'value']);
 */
class DiscordLogger
{
    /**
     * The Discord webhook URL.
     */
    protected ?string $webhookUrl;

    /**
     * Create a new DiscordLogger instance.
     */
    public function __construct()
    {
        $this->webhookUrl = config('services.discord.webhook_url');
    }

    /**
     * Send an info-level message to Discord.
     */
    public function info(string $message, array $context = []): void
    {
        $this->send(':information_source: **INFO** - ' . $message, $context, 3447003);
    }

    /**
     * Send an error-level message to Discord.
     */
    public function error(string $message, array $context = []): void
    {
        $this->send(':x: **ERROR** - ' . $message, $context, 15158332);
    }

    /**
     * Send a warning-level message to Discord.
     */
    public function warning(string $message, array $context = []): void
    {
        $this->send(':warning: **WARNING** - ' . $message, $context, 15105570);
    }

    /**
     * Send the message to the Discord webhook.
     *
     * Non-blocking: fires and forgets with try/catch to prevent disrupting the application.
     */
    protected function send(string $message, array $context, int $color): void
    {
        if (empty($this->webhookUrl)) {
            return;
        }

        try {
            $embed = [
                'description' => $message,
                'color' => $color,
                'timestamp' => now()->toIso8601String(),
                'footer' => [
                    'text' => 'CityPulse Logger',
                ],
            ];

            if (!empty($context)) {
                $fields = [];
                foreach ($context as $key => $value) {
                    $fields[] = [
                        'name' => (string) $key,
                        'value' => is_string($value) ? $value : json_encode($value, JSON_PRETTY_PRINT),
                        'inline' => true,
                    ];
                }
                $embed['fields'] = $fields;
            }

            Http::timeout(5)->post($this->webhookUrl, [
                'embeds' => [$embed],
            ]);
        } catch (\Throwable $e) {
            // Fire and forget - log locally but don't disrupt the application
            Log::warning('Failed to send Discord log message: ' . $e->getMessage());
        }
    }
}

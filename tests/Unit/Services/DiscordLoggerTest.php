<?php

namespace Tests\Unit\Services;

use App\Services\DiscordLogger;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordLoggerTest extends TestCase
{
    public function test_discord_logger_can_be_instantiated(): void
    {
        $logger = new DiscordLogger();

        $this->assertInstanceOf(DiscordLogger::class, $logger);
    }

    public function test_info_method_exists(): void
    {
        $logger = new DiscordLogger();

        $this->assertTrue(method_exists($logger, 'info'));
    }

    public function test_error_method_exists(): void
    {
        $logger = new DiscordLogger();

        $this->assertTrue(method_exists($logger, 'error'));
    }

    public function test_warning_method_exists(): void
    {
        $logger = new DiscordLogger();

        $this->assertTrue(method_exists($logger, 'warning'));
    }

    public function test_does_not_send_when_webhook_url_is_null(): void
    {
        Http::fake();

        config(['services.discord.webhook_url' => null]);

        $logger = new DiscordLogger();
        $logger->info('Test message', ['key' => 'value']);

        Http::assertNothingSent();
    }

    public function test_sends_info_message_to_discord_webhook(): void
    {
        Http::fake([
            'https://discord.com/api/webhooks/*' => Http::response([], 204),
        ]);

        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/test/token']);

        $logger = new DiscordLogger();
        $logger->info('Test info message', ['user' => 'test-uuid']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $embed = $body['embeds'][0] ?? [];

            return str_contains($embed['description'] ?? '', 'INFO')
                && str_contains($embed['description'] ?? '', 'Test info message')
                && isset($embed['fields'])
                && $embed['fields'][0]['name'] === 'user'
                && $embed['fields'][0]['value'] === 'test-uuid';
        });
    }

    public function test_sends_error_message_to_discord_webhook(): void
    {
        Http::fake([
            'https://discord.com/api/webhooks/*' => Http::response([], 204),
        ]);

        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/test/token']);

        $logger = new DiscordLogger();
        $logger->error('Test error message');

        Http::assertSent(function ($request) {
            $body = $request->data();
            $embed = $body['embeds'][0] ?? [];

            return str_contains($embed['description'] ?? '', 'ERROR')
                && str_contains($embed['description'] ?? '', 'Test error message');
        });
    }

    public function test_sends_warning_message_to_discord_webhook(): void
    {
        Http::fake([
            'https://discord.com/api/webhooks/*' => Http::response([], 204),
        ]);

        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/test/token']);

        $logger = new DiscordLogger();
        $logger->warning('Test warning message');

        Http::assertSent(function ($request) {
            $body = $request->data();
            $embed = $body['embeds'][0] ?? [];

            return str_contains($embed['description'] ?? '', 'WARNING')
                && str_contains($embed['description'] ?? '', 'Test warning message');
        });
    }

    public function test_does_not_throw_when_webhook_fails(): void
    {
        Http::fake([
            'https://discord.com/api/webhooks/*' => Http::response([], 500),
        ]);

        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/test/token']);

        $logger = new DiscordLogger();

        // Should not throw
        $logger->info('Test message');

        $this->assertTrue(true);
    }

    public function test_embed_contains_footer_and_timestamp(): void
    {
        Http::fake([
            'https://discord.com/api/webhooks/*' => Http::response([], 204),
        ]);

        config(['services.discord.webhook_url' => 'https://discord.com/api/webhooks/test/token']);

        $logger = new DiscordLogger();
        $logger->info('Test message');

        Http::assertSent(function ($request) {
            $body = $request->data();
            $embed = $body['embeds'][0] ?? [];

            return isset($embed['footer']['text'])
                && $embed['footer']['text'] === 'CityPulse Logger'
                && isset($embed['timestamp']);
        });
    }
}

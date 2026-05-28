<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

/**
 * Guards the scaffolding for NativePHP for Mobile.
 *
 * NativePHP itself is a commercial package and is NOT vendored in this repo;
 * however, the project ships the configuration hooks needed to install it
 * (Composer repository, env vars, ignored output directories, install guide).
 * These tests fail loudly if any of those hooks regresses.
 */
class NativePhpScaffoldingTest extends TestCase
{
    public function test_composer_json_declares_nativephp_repository(): void
    {
        $composer = json_decode(
            file_get_contents(base_path('composer.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertArrayHasKey('repositories', $composer);

        $urls = array_column($composer['repositories'], 'url');
        $this->assertContains(
            'https://nativephp.composer.sh',
            $urls,
            'The NativePHP private Composer repository must be declared so that "composer require nativephp/mobile" can find the package.'
        );
    }

    public function test_env_example_documents_nativephp_variables(): void
    {
        $env = file_get_contents(base_path('.env.example'));

        foreach ([
            'NATIVEPHP_APP_ID',
            'NATIVEPHP_APP_VERSION',
            'NATIVEPHP_APP_VERSION_CODE',
            'NATIVEPHP_DEVELOPMENT_TEAM',
            'NATIVEPHP_DEEPLINKING_ENABLED',
            'NATIVEPHP_DEEPLINKING_SCHEME',
        ] as $key) {
            $this->assertStringContainsString($key, $env, "Missing {$key} in .env.example");
        }
    }

    public function test_gitignore_excludes_nativephp_artefacts(): void
    {
        $ignore = file_get_contents(base_path('.gitignore'));

        $this->assertStringContainsString('/nativephp', $ignore);
        $this->assertStringContainsString('/.nativephp', $ignore);
    }

    public function test_install_guide_is_present(): void
    {
        $this->assertFileExists(base_path('docs/native-php-mobile.md'));
    }
}

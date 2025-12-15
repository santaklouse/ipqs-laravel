<?php

namespace IpqsLaravel;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class IpqsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Объединяем конфигурацию по умолчанию с конфигурацией пользователя
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ipqs.php', 'ipqs'
        );

        // Регистрируем наш сервис в контейнере как синглтон
        $this->app->singleton(IpqsService::class, function ($app) {
            $config = $app['config']->get('ipqs');

            return new IpqsService(
                new Client(['timeout' => $config['timeout'] ?? 10.0]),
                $config['api_key'] ?? null
            );
        });
    }

    public function boot(): void
    {
        // Позволяет пользователю опубликовать файл конфигурации
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ipqs.php' => config_path('ipqs.php'),
            ], 'ipqs-config');
        }
    }
}

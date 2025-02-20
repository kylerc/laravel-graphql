<?php

namespace Audentio\LaravelGraphQL\Providers;

use Audentio\LaravelBase\Traits\ExtendServiceProviderTrait;
use Audentio\LaravelGraphQL\GraphQL\Console\ConfigGraphqlCommand;
use Audentio\LaravelGraphQL\GraphQL\Console\EnumMakeCommand;
use Audentio\LaravelGraphQL\GraphQL\Console\ResourceMakeCommand;
use Audentio\LaravelGraphQL\GraphQL\Debugger\QueriesExecutedDebugger;
use Audentio\LaravelGraphQL\Illuminate\Foundation\Console\ModelMakeCommand;
use Audentio\LaravelGraphQL\LaravelGraphQL;
use Audentio\LaravelGraphQL\Rebing\GraphQL\Console\MutationMakeCommand;
use Audentio\LaravelGraphQL\Rebing\GraphQL\Console\QueryMakeCommand;
use Audentio\LaravelGraphQL\Rebing\GraphQL\Console\TypeMakeCommand;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class GraphQLServiceProvider extends ServiceProvider
{
    use ExtendServiceProviderTrait;

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->bootConsole();
        }

        $this->bootPublishes();
        $this->bootDebug();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/audentioGraphQL.php', 'audentioGraphQL'
        );

        $this->registerDebug();
    }

    public function provides()
    {
        $provides = [];

        if (LaravelGraphQL::isDebugEnabled()) {
            $provides[] = QueriesExecutedDebugger::class;
        }

        return $provides;
    }

    protected function bootDebug(): void
    {
        if (LaravelGraphQL::isDebugEnabled()) {
            DB::listen(function(QueryExecuted $query) {
                $this->app->get(QueriesExecutedDebugger::class)->push($query);
            });
        }
    }

    protected function bootConsole()
    {
        $this->extendRebing();
        $this->extendBase();

        $this->commands(EnumMakeCommand::class);
        $this->commands(ConfigGraphqlCommand::class);
        $this->commands(ResourceMakeCommand::class);
    }

    protected function extendBase()
    {
         $this->overrideIlluminateCommand('command.model.make', ModelMakeCommand::class);
    }

    protected function extendRebing()
    {
        $this->commands(TypeMakeCommand::class);
        $this->commands(MutationMakeCommand::class);
        $this->commands(QueryMakeCommand::class);
    }

    protected function bootPublishes(): void
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('graphql.php'),
            __DIR__.'/../../config/audentioGraphQL.php' => config_path('audentioGraphQL.php'),
        ], 'config');
    }

    protected function registerDebug(): void
    {
        $this->app->singleton(QueriesExecutedDebugger::class, QueriesExecutedDebugger::class);
    }
}
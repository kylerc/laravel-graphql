<?php

namespace Audentio\LaravelGraphQL\GraphQL\Console;

use Audentio\LaravelBase\Traits\ExtendConsoleCommandTrait;
use Audentio\LaravelGraphQL\Rebing\GraphQL\Console\TypeMakeCommand;
use Illuminate\Console\GeneratorCommand;

class ResourceMakeCommand extends GeneratorCommand
{
    use ExtendConsoleCommandTrait;

    protected $signature = 'make:graphql:resource {name}';
    protected $description = 'Create a new GraphQL resource class';
    protected $type = 'Resource';

    protected function getStub()
    {
        return __DIR__.'/stubs/resource.stub';
    }

    protected function qualifyClass($name)
    {
        $name = $this->suffixCommandClass($name, 'Resource');

        return parent::qualifyClass($name);
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\GraphQL\Resources';
    }

    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);
        $stub = $this->replaceGraphqlName($stub);

        $modelClass = '';
        $modelName = class_basename($name);
        $modelName = substr($modelName, 0, -8);

        if (class_exists('App\Models\\' . $modelName)) {
            $modelClass = 'App\Models\\' . $modelName;
        }

        $stub = $this->replaceModelName($stub, $modelName, $modelClass);

        return $stub;
    }

    protected function replaceModelName(string $stub, string $modelName = null, string $modelClass = null): string
    {
        $replacements = [];

        if ($modelClass) {
           $replacements['{modelInclude}'] = 'use ' . $modelClass . ";\n";
            $replacements['{modelClassName}'] = 'return ' . $modelName . '::class;';
        } else {
            $replacements['{modelInclude}'] = '';
            $replacements['{modelClassName}'] = 'return null;';
        }

        $replacements['{graphQLTypeName}'] = 'return \'' . $modelName . '\';';

        $stub = str_replace(array_keys($replacements), array_values($replacements), $stub);

        return $stub;
    }

    protected function replaceGraphqlName(string $stub): string
    {
        $graphqlName = $this->getNameInput();
        $graphqlName = preg_replace('/Type$/', '', $graphqlName);

        return str_replace(
            'DummyGraphqlName',
            $graphqlName,
            $stub
        );
    }
}
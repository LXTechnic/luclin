<?php

namespace Luclin\Providers;

use Luclin\Cabin\Foundation\ConnectionFactory;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use \Illuminate\Support\Fluent;

class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Builder::macro('contains', function (string $field, ...$values) {
            $this->whereRaw("$field @> '{".implode(',', $values)."}'");
            return $this;
        });
        Builder::macro('notContains', function (string $field, ...$values) {
            $this->whereRaw("not ($field && '{".implode(',', $values)."}')");
            return $this;
        });
        Builder::macro('congruent', function (string $field, ...$values) {
            $this->whereRaw("$field = '{".implode(',', $values)."}'");
            return $this;
        });

        PostgresGrammar::macro('type_varchar', function(Fluent $column) {
            return "varchar[]";
        });

        PostgresGrammar::macro('type_integer', function(Fluent $column) {
            return "int8[]";
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });
    }
}

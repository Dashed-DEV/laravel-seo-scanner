<?php

namespace Dashed\Seo;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\Seo\Commands\SeoScan;
use Dashed\Seo\Commands\SeoScanUrl;

class SeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-seo')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations(['create_seo_scans_table', 'create_seo_score_table'])
            ->hasCommands([
                SeoScan::class,
                SeoScanUrl::class,
            ]);

        // When testing, we can ignore this code
        if (app()->runningUnitTests()) {
            return;
        }

        $package
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('dashed/laravel-seo');
            });
    }
}

<?php

namespace Dashed\Seo\Checks\Configuration;

use Illuminate\Http\Client\Response;
use Symfony\Component\DomCrawler\Crawler;
use Dashed\Seo\Interfaces\Check;
use Dashed\Seo\Traits\PerformCheck;
use Dashed\Seo\Traits\Translatable;

class NoFollowCheck implements Check
{
    use PerformCheck,
        Translatable;

    public string $title = "The page does not have 'nofollow' set";

    public string $description = "When the page has the 'nofollow' tag or meta tag set, search engines will not follow the links on the page.";

    public string $priority = 'low';

    public int $timeToFix = 5;

    public int $scoreWeight = 5;

    public bool $continueAfterFailure = false;

    public ?string $failureReason;

    public mixed $actualValue = null;

    public mixed $expectedValue = null;

    public function check(Response $response, Crawler $crawler): bool
    {
        if ($response->header('X-Robots-Tag') === 'nofollow') {
            $this->failureReason = __('failed.configuration.nofollow.tag');

            return false;
        }

        if (! $this->validateContent($crawler)) {
            return false;
        }

        return true;
    }

    public function validateContent(Crawler $crawler): bool
    {
        if (! $crawler->filterXPath('//meta[@name="robots"]')->getNode(0) &&
            ! $crawler->filterXPath('//meta[@name="googlebot"]')->getNode(0)
        ) {
            return true;
        }

        $robotContent = $crawler->filterXPath('//meta[@name="robots"]')->each(function (Crawler $node, $i) {
            return $node->attr('content');
        });

        $googlebotContent = $crawler->filterXPath('//meta[@name="googlebot"]')->each(function (Crawler $node, $i) {
            return $node->attr('content');
        });

        $content = array_merge($robotContent, $googlebotContent);

        foreach ($content as $tag) {
            if (str_contains($tag, 'nofollow')) {
                $this->failureReason = __('failed.configuration.nofollow.meta');

                return false;
            }
        }

        return true;
    }
}

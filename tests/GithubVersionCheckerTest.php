<?php

namespace GlpiPlugin\Securityincidents\Tests;

use GlpiPlugin\Securityincidents\Services\GithubVersionChecker;

/**
 * No real network call in this suite (CI has no guaranteed egress to api.github.com, and a real
 * call would fail intermittently for reasons unrelated to this plugin's own code) — exercises the
 * cache-read path only, priming `$GLPI_CACHE` directly with the exact key the class itself uses.
 * The write path (a real GitHub API call on cache miss) is exactly the same
 * `Toolbox::getURLContent()` mechanism already covered by GLPI core's own test suite for
 * `Toolbox::checkNewVersionAvailable()` — not re-tested here.
 */
final class GithubVersionCheckerTest extends SecurityIncidentsTestCase
{
    private const CACHE_KEY = 'plugin_securityincidents_latest_github_version';

    protected function tearDown(): void {
        global $GLPI_CACHE;
        $GLPI_CACHE->delete(self::CACHE_KEY);

        parent::tearDown();
    }

    public function testReturnsTheCachedVersionWithoutHittingGithub(): void {
        global $GLPI_CACHE;
        $GLPI_CACHE->set(self::CACHE_KEY, '1.2.3', DAY_TIMESTAMP);

        $this->assertSame('1.2.3', GithubVersionChecker::getLatestGithubVersion());
    }

    /**
     * A previous failed lookup caches an empty string (see the class's own docblock) rather than
     * leaving the cache unset, specifically to avoid hammering GitHub's rate-limited API again on
     * every single page render until the next real successful call.
     */
    public function testACachedEmptyStringMeansNoVersionRatherThanARetry(): void {
        global $GLPI_CACHE;
        $GLPI_CACHE->set(self::CACHE_KEY, '', DAY_TIMESTAMP);

        $this->assertNull(GithubVersionChecker::getLatestGithubVersion());
    }
}

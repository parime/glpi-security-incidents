<?php

/**
 * -------------------------------------------------------------------------
 * Security Incidents plugin for GLPI
 * Copyright (C) 2026 Vincent GUILLOTTE
 * https://github.com/parime/glpi-security-incidents
 * -------------------------------------------------------------------------
 * LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version. See LICENSE for the full text.
 * -------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace GlpiPlugin\Securityincidents\Services;

/**
 * Latest published GitHub release (for display next to the installed version on the Config
 * screen) — same mechanism as the sibling plugins (`Config::getLatestGithubVersion()` on
 * Configuration-glpi-auto, `GithubVersionChecker` on glpi-iso27001-management), reused as-is
 * rather than reinvented.
 *
 * Cached 24h (same duration/mechanism as GLPI core's own `RSSFeed::getRSSFeed()`, `$GLPI_CACHE`):
 * the unauthenticated GitHub API is limited to 60 requests/hour per IP, nowhere near enough if
 * called on every page render. `Toolbox::getURLContent()` (not a raw HTTP call): reuses the
 * proxy/timeout/error handling GLPI core already established for this kind of call — same
 * function `Toolbox::checkNewVersionAvailable()` uses for GLPI's own update check.
 */
final class GithubVersionChecker
{
    /**
     * @return string|null Version number (without the tag's leading "v"), or null if the call
     *         failed (no connectivity, GitHub API unavailable...).
     */
   public static function getLatestGithubVersion(): ?string {
       global $GLPI_CACHE;

       $cacheKey = 'plugin_securityincidents_latest_github_version';
       $cached = $GLPI_CACHE->get($cacheKey);
      if ($cached !== null) {
          return $cached === '' ? null : $cached;
      }

       $error = '';
       // CURLOPT_TIMEOUT (total request time) alongside core's own CURLOPT_CONNECTTIMEOUT=5
       // default: on a network with no egress to github.com, the connect timeout alone doesn't
       // bound a host that accepts the TCP connection but never answers — this config-page-render
       // call would otherwise stall indefinitely instead of failing after a few seconds.
       $json = \Toolbox::getURLContent(
           'https://api.github.com/repos/parime/glpi-security-incidents/releases/latest',
           $error,
           0,
           [CURLOPT_TIMEOUT => 5]
       );
       $version = null;
      if (!empty($json)) {
          $data = json_decode($json, true);
         if (is_array($data) && !empty($data['tag_name']) && is_string($data['tag_name'])) {
            $version = ltrim($data['tag_name'], 'v');
         }
      }

       $GLPI_CACHE->set($cacheKey, $version ?? '', DAY_TIMESTAMP);

       return $version;
   }
}

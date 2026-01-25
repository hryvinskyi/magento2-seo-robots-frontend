<?php
/**
 * Copyright (c) 2026. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\SeoRobotsFrontend\Test\Mftf\Helper;

use Magento\FunctionalTestingFramework\Helper\Helper;

/**
 * Helper for checking HTTP response headers in MFTF tests.
 */
class HttpHeaderHelper extends Helper
{
    /**
     * Asserts that the X-Robots-Tag header is present in the HTTP response.
     *
     * @param string $url The URL to check
     * @return void
     * @throws \Exception If the header is not found
     */
    public function assertXRobotsTagHeader(string $url, bool $headerExist = true, ?string $headerValue = null): void
    {
        $baseUrl = rtrim(getenv('MAGENTO_BASE_URL') ?: 'http://localhost/', '/');
        $fullUrl = $baseUrl . '/' . ltrim($url, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("Failed to fetch URL: {$fullUrl}");
        }

        if ($httpCode >= 400) {
            throw new \Exception("HTTP request failed with code {$httpCode} for URL: {$fullUrl}");
        }

        $headers = $this->parseHeaders($response);

        if ($headerExist) {
            if (!isset($headers['x-robots-tag'])) {
                throw new \Exception(
                    "X-Robots-Tag header not found in response from {$fullUrl}. " .
                    "Available headers: " . implode(', ', array_keys($headers))
                );
            }

            if ($headerValue !== null && $headers['x-robots-tag'] !== $headerValue) {
                throw new \Exception(
                   "X-Robots-Tag header found in response from {$fullUrl}, but does not match expected value. " .
                    "Expected: {$headerValue}, Actual: {$headers['x-robots-tag']}"
                );
            }

            return;
        }

        if (isset($headers['x-robots-tag'])) {
            throw new \Exception(
                "X-Robots-Tag header present {$fullUrl}. " .
                "Header: " . $headers['x-robots-tag']
            );
        }
    }

    /**
     * Asserts that the X-Robots-Tag header contains a specific value.
     *
     * @param string $url The URL to check
     * @param string $expectedValue The expected header value (or part of it)
     * @return void
     * @throws \Exception If the header is not found or doesn't contain expected value
     */
    public function assertXRobotsTagHeaderContains(string $url, string $expectedValue): void
    {
        $baseUrl = rtrim(getenv('MAGENTO_BASE_URL') ?: 'http://localhost/', '/');
        $fullUrl = $baseUrl . '/' . ltrim($url, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("Failed to fetch URL: {$fullUrl}");
        }

        if ($httpCode >= 400) {
            throw new \Exception("HTTP request failed with code {$httpCode} for URL: {$fullUrl}");
        }

        $headers = $this->parseHeaders($response);

        if (!isset($headers['x-robots-tag'])) {
            throw new \Exception(
                "X-Robots-Tag header not found in response from {$fullUrl}. " .
                "Available headers: " . implode(', ', array_keys($headers))
            );
        }

        if (stripos($headers['x-robots-tag'], $expectedValue) === false) {
            throw new \Exception(
                "X-Robots-Tag header does not contain '{$expectedValue}'. " .
                "Actual value: '{$headers['x-robots-tag']}'"
            );
        }
    }

    /**
     * Asserts that a 404 page has the X-Robots-Tag header.
     *
     * @param string $url The URL to check (should return 404)
     * @param bool $isTrue Whether the header should be present (true) or absent (false)
     * @return void
     * @throws \Exception If the page is not 404 or header assertion fails
     */
    public function assert404XRobotsTagHeader(string $url, bool $isTrue = true): void
    {
        $baseUrl = rtrim(getenv('MAGENTO_BASE_URL') ?: 'http://localhost/', '/');
        $fullUrl = $baseUrl . '/' . ltrim($url, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("Failed to fetch URL: {$fullUrl}");
        }

        if ($httpCode !== 404) {
            throw new \Exception("Expected HTTP 404 but got {$httpCode} for URL: {$fullUrl}");
        }

        $headers = $this->parseHeaders($response);

        if ($isTrue) {
            if (!isset($headers['x-robots-tag'])) {
                throw new \Exception(
                    "X-Robots-Tag header not found in 404 response from {$fullUrl}. " .
                    "Available headers: " . implode(', ', array_keys($headers))
                );
            }
        } else {
            if (isset($headers['x-robots-tag'])) {
                throw new \Exception(
                    "X-Robots-Tag header should not be present in 404 response from {$fullUrl}. " .
                    "Header value: " . $headers['x-robots-tag']
                );
            }
        }
    }

    /**
     * Asserts that a 404 page has the X-Robots-Tag header with a specific value.
     *
     * @param string $url The URL to check (should return 404)
     * @param string $expectedValue The expected header value (or part of it)
     * @return void
     * @throws \Exception If the page is not 404, header is missing, or value doesn't match
     */
    public function assert404XRobotsTagHeaderContains(string $url, string $expectedValue): void
    {
        $baseUrl = rtrim(getenv('MAGENTO_BASE_URL') ?: 'http://localhost/', '/');
        $fullUrl = $baseUrl . '/' . ltrim($url, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("Failed to fetch URL: {$fullUrl}");
        }

        if ($httpCode !== 404) {
            throw new \Exception("Expected HTTP 404 but got {$httpCode} for URL: {$fullUrl}");
        }

        $headers = $this->parseHeaders($response);

        if (!isset($headers['x-robots-tag'])) {
            throw new \Exception(
                "X-Robots-Tag header not found in 404 response from {$fullUrl}. " .
                "Available headers: " . implode(', ', array_keys($headers))
            );
        }

        if (stripos($headers['x-robots-tag'], $expectedValue) === false) {
            throw new \Exception(
                "X-Robots-Tag header does not contain '{$expectedValue}' in 404 response. " .
                "Actual value: '{$headers['x-robots-tag']}'"
            );
        }
    }

    /**
     * Parses HTTP response headers into an associative array.
     *
     * @param string $headerContent Raw header content
     * @return array<string, string> Parsed headers with lowercase keys
     */
    private function parseHeaders(string $headerContent): array
    {
        $headers = [];
        $lines = explode("\r\n", $headerContent);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }

        return $headers;
    }
}

<?php

/**
 * Tests for GlobalConfig. Must cover all of its methods, and use at least one file configuration that contains
 * different values than the default ones.
 */
class GlobalConfigTest extends PHPUnit\Framework\TestCase
{
    /** @var GlobalConfig */
    private $config;
    /** @var GlobalConfig */
    private $configWithDefaults;
    /** @var GlobalConfig */
    private $configWithBaseHref;

    protected function setUp(): void
    {
        $this->config = (new Model('/../../tests/testconfig.ttl'))->getConfig();
        $this->assertNotNull($this->config->getCache());
        $this->assertNotNull($this->config->getGraph());
        $this->configWithDefaults = (new Model('/../../tests/testconfig-fordefaults.ttl'))->getConfig();
        $this->configWithBaseHref = (new Model('/../../tests/testconfig-basehref.ttl'))->getConfig();
    }

    // --- tests for values that are overriding default values

    public function testGetDefaultEndpoint()
    {
        $this->assertEquals(getenv('SKOSMOS_SPARQL_ENDPOINT'), $this->config->getDefaultEndpoint());
    }

    public function testGetDefaultSparqlDialect()
    {
        $this->assertEquals("Generic", $this->config->getDefaultSparqlDialect());
    }

    public function testGetCollationEnabled()
    {
        $this->assertEquals(true, $this->config->getCollationEnabled());
    }

    public function testGetSparqlTimeout()
    {
        $this->assertEquals(10, $this->config->getSparqlTimeout());
    }

    public function testGetHttpTimeout()
    {
        $this->assertEquals(2, $this->config->getHttpTimeout());
    }

    public function testGetServiceName()
    {
        $this->assertEquals("Skosmos being tested", $this->config->getServiceName());
    }

    public function testGetBaseHref()
    {
        $this->assertEquals("http://tests.localhost/Skosmos/", $this->configWithBaseHref->getBaseHref());
    }

    public function testGetLanguages()
    {
        $this->assertEquals(array('en' => 'en_GB.utf8', 'fi' => 'fi_FI.utf8', 'fr' => 'fr_FR.utf8', 'sv' => 'sv_SE.utf8'), $this->config->getLanguages());
    }

    public function testGetSearchResultsSize()
    {
        $this->assertEquals(5, $this->config->getSearchResultsSize());
    }

    public function testGetDefaultTransitiveLimit()
    {
        $this->assertEquals(100, $this->config->getDefaultTransitiveLimit());
    }

    public function testGetLogCaughtExceptions()
    {
        $this->assertEquals(true, $this->config->getLogCaughtExceptions());
    }

    public function testGetLoggingBrowserConsole()
    {
        $this->assertEquals(true, $this->config->getLoggingBrowserConsole());
    }

    public function testGetLoggingFilename()
    {
        $this->assertEquals("/tmp/test_skosmos.log", $this->config->getLoggingFilename());
    }

    public function testGetTemplateCache()
    {
        $this->assertEquals("/tmp/skosmos-template-cache-tests", $this->config->getTemplateCache());
    }

    public function testGetCustomCss()
    {
        $this->assertEquals("resource/css/tests-stylesheet.css", $this->config->getCustomCss());
    }

    public function testGetFeedbackAddress()
    {
        $this->assertEquals("tests@skosmos.test", $this->config->getFeedbackAddress());
    }

    public function testGetFeedbackSender()
    {
        $this->assertEquals("tests skosmos", $this->config->getFeedbackSender());
    }

    public function testGetFeedbackEnvelopeSender()
    {
        $this->assertEquals("skosmos tests", $this->config->getFeedbackEnvelopeSender());
    }

    public function testGetUiLanguageDropdown()
    {
        $this->assertEquals(true, $this->config->getUiLanguageDropdown());
    }

    public function testGetHoneypotEnabled()
    {
        $this->assertEquals(false, $this->config->getHoneypotEnabled());
    }

    public function testGetHoneypotTime()
    {
        $this->assertEquals(2, $this->config->getHoneypotTime());
    }

    public function testGetGlobalPlugins()
    {
        $this->assertEquals(["alpha", "Bravo", "charlie"], $this->config->getGlobalPlugins());
    }

    // --- tests for the exception paths

    public function testInitializeConfigWithoutGraph()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('config.ttl must have exactly one skosmos:Configuration');
        $model = new Model('/../../tests/testconfig-nograph.ttl');
        $this->assertNotNull($model);
    }

    public function testNonexistentFile()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('config.ttl file is missing, please provide one.');
        $model = new Model('/../../tests/testconfig-idonotexist.ttl');
        $this->assertNotNull($model);
    }

    // --- tests for some default values

    public function testsGetDefaultLanguages()
    {
        $this->assertEquals(['en' => 'en_GB.utf8'], $this->configWithDefaults->getLanguages());
    }

    public function testGetDefaultHttpTimeout()
    {
        $this->assertEquals(5, $this->configWithDefaults->getHttpTimeout());
    }

    public function testGetDefaultFeedbackAddress()
    {
        $this->assertEquals(null, $this->configWithDefaults->getFeedbackAddress());
    }

    public function testGetDefaultLogCaughtExceptions()
    {
        $this->assertEquals(false, $this->configWithDefaults->getLogCaughtExceptions());
    }

    public function testGetDefaultServiceName()
    {
        $this->assertEquals("Skosmos", $this->configWithDefaults->getServiceName());
    }
}

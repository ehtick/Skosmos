<?php

class ConceptTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Model
     */
    private $model;
    private $vocab;
    private $concept;
    private $cbdVocab;
    private $cbdGraph;

    protected function setUp(): void
    {
        $this->model = new Model('/../../tests/testconfig.ttl');
        $this->vocab = $this->model->getVocabulary('test');
        $this->concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta112', 'en');

        $this->cbdVocab = $this->model->getVocabulary('cbd');
        $this->cbdGraph =  new EasyRdf\Graph();
        $this->cbdGraph->parseFile(__DIR__ . "/test-vocab-data/cbd.ttl", "turtle");

    }

    /**
     * @covers Concept::__construct
     */
    public function testConstructor()
    {
        $mockres = $this->getMockBuilder('EasyRdf\Resource')->disableOriginalConstructor()->getMock();
        $concept = new Concept($this->model, $this->vocab, $mockres, 'http://skosmos.skos/test', 'en');
        $this->assertInstanceOf('Concept', $concept);
        $this->assertEquals('Test ontology', $concept->getVocabTitle());
    }

    /**
     * @covers Concept::getUri
     */
    public function testGetUri()
    {
        $uri = $this->concept->getURI();
        $this->assertEquals('http://www.skosmos.skos/test/ta112', $uri);
    }

    /**
     * @covers Concept::getDeprecated
     */
    public function testGetConceptNotDeprecated()
    {
        $deprecated = $this->concept->getDeprecated();
        $this->assertEquals(false, $deprecated);
    }

    /**
     * @covers Concept::getVocab
     */
    public function testGetVocab()
    {
        $voc = $this->concept->getVocab();
        $this->assertInstanceOf('Vocabulary', $voc);
    }

    /**
     * @covers Concept::getVocabTitle
     */
    public function testGetVocabTitle()
    {
        $title = $this->concept->getVocabTitle();
        $this->assertEquals('Test ontology', $title);
    }

    /**
     * @covers Concept::getShortName
     */
    public function testGetShortName()
    {
        $short = $this->concept->getShortName();
        $this->assertEquals('Test short', $short);
    }

    /**
     * @covers Concept::getFoundBy
     */
    public function testGetFoundByWhenNotSet()
    {
        $fb = $this->concept->getFoundBy();
        $this->assertEquals(null, $fb);
    }

    /**
     * @covers Concept::setFoundBy
     * @covers Concept::getFoundByType
     */
    public function testSetFoundBy()
    {
        $fb = $this->concept->getFoundBy();
        $this->assertEquals(null, $fb);
        $this->concept->setFoundBy('testing matched label', 'alt');
        $fb = $this->concept->getFoundBy();
        $fbtype = $this->concept->getFoundByType();
        $this->assertEquals('testing matched label', $fb);
        $this->assertEquals('alt', $fbtype);
    }

    /**
     * @covers Concept::getForeignLabels
     * @covers Concept::getForeignLabelList
     * @covers Concept::langToString
     */
    public function testGetForeignLabels()
    {
        # make sure UK English collation is being used as the default
        setlocale(LC_ALL, "en_GB.utf8");

        $labels = $this->concept->getForeignLabels();

        $this->assertEquals('Karppi', $labels['Finnish']['prefLabel'][0]->getLabel());
        $this->assertArrayNotHasKey('altLabel', $labels['Finnish']);
        $this->assertArrayNotHasKey('English', $labels);

        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta115', 'en');
        $this->assertEmpty($concept->getForeignLabels());

        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta127', 'en');
        $labels = $concept->getForeignLabels();
        $this->assertEquals(['', 'Finnish', 'Swedish'], array_keys($labels));

        $this->assertEquals("Ä before 'A first' in English (default) collation", $labels['']['prefLabel'][0]->getLabel());
        $this->assertEquals("Test sorting labels 2", $labels['']['prefLabel'][3]->getLabel());

        $this->assertArrayHasKey('prefLabel', $labels['Finnish']);
        $fiAltLabels = array_map(function ($elem) {return $elem->getLabel();}, $labels['Finnish']['altLabel']);
        $fiCorrectSort = ["A sort first", "B sorts second", "Ä way after B in Finnish collation"];
        $this->assertEquals($fiCorrectSort, $fiAltLabels);
    }

    /**
     * @covers Concept::getAllLabels
     */
    public function testGetAllLabels()
    {
        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta115', 'en');
        $labels = $concept->getAllLabels('skos:definition');
        $this->assertEquals('Iljettävä limanuljaska', $labels['Finnish'][0]->getLabel());
        $this->assertEquals('any fish belonging to the order Anguilliformes', $labels['English'][0]->getLabel());
    }

    /**
     * @covers Concept::getProperties
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValue::getLabel
     */
    public function testGetPropertiesLiteralValue()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['http://www.skosmos.skos/testprop']->getValues();

        $this->assertEquals('Skosmos test property', $props['http://www.skosmos.skos/testprop']->getLabel()->getValue());
        $this->assertEquals('Test property value', $propvals['Test property value']->getLabel());
    }

    /**
     * @covers Concept::getProperties
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValue::getLabel
     */
    public function testGetPropertiesCorrectNumberOfProperties()
    {
        $props = $this->concept->getProperties();

        $this->assertEquals(9, sizeof($props));
    }

    /**
     * @covers DataObject::arbitrarySort
     * @covers DataObject::mycompare
     * @covers Concept::getProperties
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValue::getLabel
     */
    public function testGetPropertiesCorrectOrderOfProperties()
    {
        $props = $this->concept->getProperties();
        $expected = array(0 => 'rdf:type', 1 => 'skos:broader', 2 => 'skos:narrower', 3 => 'skos:altLabel',
            4 => 'skos:scopeNote', 5 => 'http://www.skosmos.skos/multiLingOff', 6 => 'http://www.skosmos.skos/multiLingOn',
            7 => 'http://www.skosmos.skos/testprop', 8 => 'skos:notation');
        $this->assertEquals($expected, array_keys($props));

    }

    /**
     * @covers Concept::getProperties
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValue::getLabel
     */
    public function testGetPropertiesAlphabeticalSortingOfPropertyValues()
    {
        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta1', 'en');
        $props = $concept->getProperties();
        $prevlabel = null;
        foreach($props['skos:narrower']->getValues() as $val) {
            $label = $val->getLabel();
            if ($prevlabel) {
                $this->assertEquals(-1, strnatcasecmp($prevlabel, $label));
            }
            $prevlabel = $label;
        }
    }

    /**
     * @covers Concept::getProperties
     * @covers Concept::removeDuplicatePropertyValues
     */
    public function testGetPropertiesOfDeprecatedConcept()
    {
        $concept = $this->vocab->getConceptInfo('http://www.skosmos.skos/test/ta111', 'en');
        $props = $concept->getProperties();
        $this->assertNotContains('owl:deprecated', $props);
    }

    /**
     * @covers Concept::getMappingProperties
     * @covers ConceptProperty::getValues
     */
    public function testGetMappingPropertiesWithIdenticalLabels()
    {
        $vocab = $this->model->getVocabulary('duplicates');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/dup/d3", "en");
        // suppress fsockopen PHP warnings caused by trying to resolve www.skosmos.skos
        @$props = $concept->getMappingProperties();
        $values = $props['skos:closeMatch']->getValues();
        $this->assertCount(2, $values);
    }

    /**
     * @covers Concept::removeDuplicatePropertyValues
     * @covers Concept::getProperties
     */
    public function testRemoveDuplicatePropertyValues()
    {
        $vocab = $this->model->getVocabulary('duplicates');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/dup/d4", "en");
        $props = $concept->getProperties();
        $this->assertCount(1, $props);
    }

    /**
     * @covers Concept::removeDuplicatePropertyValues
     * @covers Concept::getPreferredSubpropertyLabelTranslation
     */
    public function testgetPreferredSubpropertyLabelTranslation()
    {
        $vocab = $this->model->getVocabulary('duplicates');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/dup/d6", "en");
        $this->assertEquals($concept->getPreferredSubpropertyLabelTranslation('en'), "Subproperty of skos:prefLabel");
        $this->assertEquals($concept->getPreferredSubpropertyLabelTranslation('fi'), null);
    }

    /**
     * @covers Concept::removeDuplicatePropertyValues
     * @covers Concept::getProperties
     */
    public function testRemoveDuplicateValuesForPreflabel()
    {
        $vocab = $this->model->getVocabulary('duplicates');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/dup/d7", "en");
        $props = $concept->getProperties();
        $this->assertCount(0, $props);
    }

    /**
     * @covers Concept::removeDuplicatePropertyValues
     * @covers Concept::getProperties
     */
    public function testRemoveDuplicatePropertyValuesOtherThanSubpropertyof()
    {
        $vocab = $this->model->getVocabulary('duplicates');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/dup/d5", "en");
        $props = $concept->getProperties();
        $this->assertCount(2, $props);
    }

    /**
     * @covers Concept::getProperties
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValueLiteral::getLabel
     */
    public function testGetTimestamp()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/test/ta123", "en");
        $date = $concept->getDate();
        $this->assertStringContainsString('10/1/14', $date);
    }

    /**
     * @covers Concept::getDate
     */
    public function testGetDateWithCreatedAndModified()
    {
        $vocab = $this->model->getVocabulary('dates');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/date/d1", "en");
        $date = $concept->getDate();
        $this->assertStringContainsString('1/3/00', $date);
        $this->assertStringContainsString('6/6/12', $date);
    }

    /**
     * @covers Concept::getDate
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValueLiteral::getLabel
     */
    public function testGetTimestampInvalidWarning()
    {
        set_error_handler(
            static function ( $errno, $errstr ) {
                restore_error_handler();
                throw new UserWarning( $errstr, $errno );
            },
            E_ALL
        );

        $vocab = $this->model->getVocabulary('test');

        $this->expectException(UserWarning::class);
        $this->expectExceptionMessage("Failed to parse time string (1986-21-00) at position 6 (1): Unexpected character");

        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/test/ta114", "en");
        $concept->getDate(); # this should throw an ErrorException
    }

    /**
     * @covers Concept::getProperties
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValueLiteral::getLabel
     */

    public function testGetTimestampInvalidResult()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/test/ta114", "en");
        # we use @ to suppress the exceptions in order to be able to check the result
        $date = @$concept->getDate();
        $this->assertStringContainsString('1986-21-00', $date);
    }

    /**
     * @covers Concept::getProperties
     */
    public function testGetPropertiesTypes()
    {
        $props = $this->concept->getProperties();
        $propvals = $props['rdf:type']->getValues();
        $this->assertCount(1, $propvals); // should only have type meta:TestClass, not skos:Concept (see #200)
        $this->assertEquals('Test class', $propvals['Test class http://www.skosmos.skos/test-meta/TestClass']->getLabel());
        $this->assertEquals('http://www.skosmos.skos/test-meta/TestClass', $propvals['Test class http://www.skosmos.skos/test-meta/TestClass']->getUri());
    }

    /**
     * @covers Concept::getNotation
     */
    public function testGetNotationWhenNull()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/test/ta114", "en");
        $this->assertEquals(null, $concept->getNotation());
    }

    /**
     * @covers Concept::getNotation
     */
    public function testGetNotation()
    {
        $this->assertEquals('665', $this->concept->getNotation());
    }

    /**
     * @covers Concept::getLabel
     */
    public function testGetLabelCurrentLanguage()
    {
        $this->assertEquals('Carp', $this->concept->getLabel()->getValue());
    }

    /**
     * @covers Concept::getLabel
     */
    public function testGetLabelWhenNull()
    {
        $model = new Model('/../../tests/testconfig.ttl');
        $vocab = $model->getVocabulary('test');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/test/ta120", "en");
        $this->assertEquals(null, $concept->getLabel());
    }

    /**
     * @covers Concept::getLabel
     * @covers Concept::setContentLang
     * @covers Concept::getContentLang
     */
    public function testGetLabelResortingToVocabDefault()
    {
        $this->concept->setContentLang('pl');
        $this->assertEquals('pl', $this->concept->getContentLang());
        $this->assertEquals('Carp', $this->concept->getLabel()->getValue());
    }

    /**
     * @covers Concept::getArrayProperties
     * @covers Concept::getGroupProperties
     * @covers Concept::getCollections
     */
    public function testGetGroupProperties()
    {
        $model = new Model('/../../tests/testconfig.ttl');
        $vocab = $model->getVocabulary('groups');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/groups/ta111", "en");
        $arrays = $concept->getArrayProperties();
        $this->assertArrayHasKey("Saltwater fish", $arrays);
        $this->assertArrayHasKey("Submarine-like fish", $arrays);
        $groups = $concept->getGroupProperties();
        $this->assertEmpty($groups);
    }

    /**
     * @covers Concept::getGroupProperties
     * @covers Concept::getCollections
     */
    public function testGetGroupPropertiesWithDuplicatedInformationFilteredOut()
    {
        $model = new Model('/../../tests/testconfig.ttl');
        $vocab = $model->getVocabulary('dupgroup');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/dupgroup/c1", "en");
        $groups = $concept->getGroupProperties();
        $this->assertEquals(0, sizeof($groups));
    }

    /**
     * @covers Concept::getGroupProperties
     * @covers Concept::getCollections
     */
    public function testGetGroupPropertiesWithHierarchy()
    {
        $model = new Model('/../../tests/testconfig.ttl');
        $vocab = $model->getVocabulary('dupgroup');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/dupgroup/ta111", "en");
        $groups = $concept->getGroupProperties();
        $this->assertEquals(2, sizeof($groups));
        $this->assertArrayHasKey("Animalia", $groups);
        $this->assertArrayHasKey("Biology", $groups);
    }

    /**
     * @covers Concept::getProperties
     * @covers Concept::getCollectionMembers
     * @covers ConceptProperty::getValues
     * @covers ConceptPropertyValue::getLabel
     * @covers ConceptPropertyValue::getSubMembers
     */
    public function testGetPropertiesWithNarrowersPartOfACollection()
    {
        $model = new Model('/../../tests/testconfig.ttl');
        $vocab = $model->getVocabulary('groups');
        $concept = $vocab->getConceptInfo("http://www.skosmos.skos/groups/ta1", "en");
        $props = $concept->getProperties();
        $narrowers = $props['skos:narrower']->getValues();
        $this->assertCount(3, $narrowers);
        foreach ($narrowers as $coll) {
            $subs = $coll->getSubMembers();
            if ($coll->getLabel() === "Freshwater fish") {
                $this->assertArrayHasKey("Carp", $subs);
            } elseif ($coll->getLabel() === "Saltwater Fish") {
                $this->assertArrayHasKey("Flatfish", $subs);
                $this->assertArrayHasKey("Tuna", $subs);
            } elseif ($coll->getLabel() === "Submarine-like fish") {
                $this->assertArrayHasKey("Tuna", $subs);
            }
        }
    }

    /**
     * @covers Concept::getProperties
     */
    public function testMultilingualPropertiesOnWithLangHit()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta112', 'en');
        $props = $concept->getProperties();
        $propvals = $props['http://www.skosmos.skos/multiLingOn']->getValues();
        $runner = array();
        foreach ($propvals as $propval) {
            array_push($runner, $propval->getLabel());
        }
        $compareableArray = ['English', 'Finnish', 'Without lang tag'];
        $this->assertSame($runner, $compareableArray);
    }

    /**
     * @covers Concept::getProperties
     */
    public function testMultilingualPropertiesOnWithoutLangHit()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta112', 'ru');
        $props = $concept->getProperties();
        $propvals = $props['http://www.skosmos.skos/multiLingOn']->getValues();
        $runner = array();
        foreach ($propvals as $propval) {
            array_push($runner, $propval->getLabel());
        }
        $compareableArray = ['English', 'Finnish', 'Without lang tag'];
        $this->assertSame($runner, $compareableArray);
    }

    /**
     * @covers Concept::getProperties
     */
    public function testMultilingualPropertiesOffWithLangHit()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta112', 'en');
        $props = $concept->getProperties();
        $propvals = $props['http://www.skosmos.skos/multiLingOff']->getValues();
        $runner = array();
        foreach ($propvals as $propval) {
            array_push($runner, $propval->getLabel());
        }
        $compareableArray = ['English', 'Without lang tag'];
        $this->assertSame($runner, $compareableArray);
    }

    /**
     * @covers Concept::getProperties
     */
    public function testMultilingualPropertiesOffWithoutLangHit()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta112', 'ru');
        $props = $concept->getProperties();
        $propvals = $props['http://www.skosmos.skos/multiLingOff']->getValues();
        $runner = array();
        foreach ($propvals as $propval) {
            array_push($runner, $propval->getLabel());
        }
        $compareableArray = ['Without lang tag'];
        $this->assertSame($runner, $compareableArray);
    }

    /**
     * @covers Concept::getProperties
     */
    public function testGetPropertiesDefinitionLiteral()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta115', 'en');
        $props = $concept->getProperties();
        $propvals = $props['skos:definition']->getValues();
        $this->assertEquals('any fish belonging to the order Anguilliformes', $propvals['any fish belonging to the order Anguilliformes']->getLabel());
    }

    /**
     * @covers Concept::getProperties
     * @covers ConceptProperty::getValues
     */
    public function testGetPropertiesDefinitionResource()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta122', 'en');
        $props = $concept->getProperties();
        $propvals = $props['skos:definition']->getValues();
        $this->assertEquals('The black sea bass (Centropristis striata) is an exclusively marine fish.', $propvals['The black sea bass (Centropristis striata) is an exclusively marine fish. http://www.skosmos.skos/test/black_sea_bass_def']->getLabel());
    }

    /**
     * @covers Concept::addResourceReifications
     * @covers Concept::addLiteralReifications
     */
    public function testExternalResourceReifications()
    {
        $concept = $this->cbdVocab->getConceptInfo('http://www.skosmos.skos/cbd/test2', 'en');

        $res = $this->cbdGraph->resource('http://www.skosmos.skos/cbd/test1');

        $concept->processExternalResource($res);
        $json =  $concept->dumpJsonLd();
        $error_count = substr_count($json, "REIFICATION_ERROR");
        $this->assertEquals($error_count, 0);
    }

    /**
     * @covers Concept::processExternalResource
     * @covers Concept::addExternalTriplesToGraph
     * @covers Concept::addPropertyValues
     */
    public function testProcessExternalResource()
    {
        $concept = $this->cbdVocab->getConceptInfo('http://www.skosmos.skos/cbd/test2', 'en');

        $res = $this->cbdGraph->resource('http://www.skosmos.skos/cbd/test1');

        $concept->processExternalResource($res);
        $json =  $concept->dumpJsonLd();
        $this->assertStringContainsString('HY', $json);
        $this->assertStringContainsString('AK', $json);
        $this->assertStringContainsString('OS', $json);
        $contains_count = substr_count($json, "CONTAINS");
        $this->assertEquals($contains_count, 3);
    }

    /**
     * Data provider for testGetModifiedDate test method.
     * @return array
     */
    public static function modifiedDateDataProvider()
    {
        return [
          ["cat", "2018-12-13T06:28:14", "+00:00"],  # set #0
          ["dog", "2018-12-13T06:28:14", "+00:00"],  # set #1
          ["owl", "2018-10-22T00:00:00", "+00:00"],  # set #2
          ["parrot", "2018-10-22T00:00:00", "+00:00"],  # set #3
          ["macaw", "2018-10-22T12:34:45", "+00:00"],  # set #4
          ["sloth", "2018-10-22T12:34:45", "+05:30"],  # set #5
        ];
    }

    /**
     * @covers Concept::getModifiedDate
     * @dataProvider modifiedDateDataProvider
     * @throws Exception if it fails to load the vocabulary
     */
    public function testGetModifiedDate($animal, $expected_time, $expected_timezone)
    {
        $vocab = $this->model->getVocabulary('http304');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/' . $animal, 'en');
        if (is_null($expected_time)) {
            $modifiedDate = $concept->getModifiedDate();
            $this->assertNull($modifiedDate);
        } else {
            $modifiedDate = $concept->getModifiedDate();
            $this->assertEquals($expected_time . $expected_timezone, $modifiedDate->format("c"));
        }
    }

    /**
     * @covers Concept::getModifiedDate
     */
    public function testGetModifiedDateFallbackToVocabularyModified()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta111', 'en');
        $modifiedDate = $concept->getModifiedDate();
        $this->assertEquals(new DateTime("2014-10-01T16:29:03+00:00"), $modifiedDate);
    }

    /**
     * @covers Concept::hasXlLabel
     */
    public function testHasXlLabelTrue()
    {
        $vocab = $this->model->getVocabulary('xl');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/xl/c1', 'en');
        $this->assertTrue($concept->hasXlLabel());
    }

    /**
     * @covers Concept::hasXlLabel
     */
    public function testHasXlLabelFalse()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta111', 'en');
        $this->assertFalse($concept->hasXlLabel());
    }

    /**
     * @covers Concept::getXlLabel
     * @covers LabelSkosXL::getProperties
     */
    public function testGetXlLabel()
    {
        $vocab = $this->model->getVocabulary('xl');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/xl/c1', 'en');
        $label = $concept->getXlLabel();
        $props = $label->getProperties();
        $this->assertArrayHasKey('skosxl:labelRelation', $props);
        $this->assertArrayHasKey('dc:modified', $props);
        $this->assertArrayNotHasKey('skosxl:literalForm', $props);
        $this->assertArrayNotHasKey('rdf:type', $props);
    }

    /**
     * @covers Concept::getXlLabel
     */
    public function testGetXlLabelNull()
    {
        $vocab = $this->model->getVocabulary('test');
        $concept = $vocab->getConceptInfo('http://www.skosmos.skos/test/ta111', 'en');
        $this->assertNull($concept->getXlLabel());
    }

}

<?php

require_once 'test_base.php';

/**
 * test class for Erfurt_Owl_Structured_Util_Sparql2OWL class
 **/
class Erfurt_Syntax_Manchester_Sparql2OWLTest extends Erfurt_TestCase
{
  
  function testQuery()
  {
    $val1 = Erfurt_Owl_Structured_Util_Owl2Structured::mapOWL2Structured(
      array("http://gasmarkt"), "http://www.bi-web.de/ontologies/le4sw/ns/0.3/Jahreshoechstlast");
    $this->assertEquals((string)$val1, "http://www.bi-web.de/ontologies/le4sw/ns/0.3/Gasmenge");

    $val2 = Erfurt_Owl_Structured_Util_Owl2Structured::mapOWL2Structured(
      array("http://gasmarkt"), "http://www.bi-web.de/ontologies/le4sw/ns/0.3/Einspeisenetzbetreiber");
    $this->assertEquals((string)$val2, "http://www.bi-web.de/ontologies/le4sw/ns/0.3/Transportnetzbetreiber or http://www.bi-web.de/ontologies/le4sw/ns/0.3/Verteilnetzbetreiber");

    $val3 = Erfurt_Owl_Structured_Util_Owl2Structured::mapOWL2Structured(
      array("http://gasmarkt"), "http://www.bi-web.de/ontologies/le4sw/ns/0.3/Speicher");
    // $this->assertEquals((string)$val3, "http://www.bi-web.de/ontologies/le4sw/ns/0.3/EIC exactly 1");
  }

  function TestModel()
  {
        $store = Erfurt_App::getInstance()->getStore();
        $dbUser = $store->getDbUser();
        $dbPass = $store->getDbPassword();
        Erfurt_App::getInstance()->authenticate($dbUser, $dbPass);

        $model = $store->getModel('http://gasmarkt');
        // $resource = new Erfurt_Rdf_Resource('http://ns.ontowiki.net/SysOnt/Anonymous', $model);
        var_dump(
        $model->getResource("http://www.bi-web.de/ontologies/le4sw/ns/0.3/Speicher")->serialize('turtle')//->getDescription(5)//;
        );
        // var_dump($resource->getDescription());
  }
}
?>

<?php
$dir = dirname(dirname(__FILE__));
set_include_path($dir);
require ($dir."/vendor/autoload.php");
require ($dir."/src/ConfigManager.php");


class ConfigTest extends PHPUnit_Framework_TestCase
{
    // ...

    public function getConfig($env='env-test'){
      return new FDT2k\ConfigManager(__DIR__."/config",$env);
    }

    public function testBasics(){
      $conf = $this->getConfig();
      $value = $conf->setGroup('test')->get('hello');
      $this->assertEquals($value,"world");

      $value = $conf->get('config');

      $this->assertTrue(is_array($value));

      $value = $conf->get('emptyvar');
      $this->assertFalse($value);

      $value = $conf->get('emptyvar',true);
      $this->assertTrue(empty($value));

      $value = $conf->get('undefined_var');
      $this->assertFalse($value);

      $value = $conf->get('undefined_var',true);
      $this->assertFalse($value);
    }

    public function testExtends(){
      // extends only include another environmnet
      $conf  = $this->getConfig('env-test-dev');
      $conf->setGroup('test');

      $value = $conf->get('config');

      $this->assertTrue(is_array($value));

      $value = $conf->get('emptyvar');
      $this->assertFalse($value);

      $value = $conf->get('emptyvar',true);
      $this->assertTrue(empty($value));

      $value = $conf->get('undefined_var');
      $this->assertFalse($value);

      $value = $conf->get('undefined_var',true);
      $this->assertFalse($value);


      $conf->setGroup('test-extend-file');
      $value = $conf->get('world');
      $this->assertEquals($value,'hello');


      $conf->setGroup('unexistent_group');
      $this->assertFalse($conf->get('world'));
    }



}

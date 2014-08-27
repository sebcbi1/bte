<?php

namespace SugiPHP;

class BteTest extends \PHPUnit_Framework_TestCase
{
	public function testOneParam()
	{
		$tpl = new Bte();
		$tpl->setTemplate('<h1>{title}</h1>');
		$this->assertSame("<h1>{title}</h1>", $tpl->getTemplate());
		$this->assertSame("<h1></h1>", $tpl->parse());
		$this->assertSame("<h1>{title}</h1>", $tpl->getTemplate());
		$tpl->set("title", "SugiPHP");
		$this->assertSame("<h1>SugiPHP</h1>", $tpl->parse());
		$this->assertSame("<h1>{title}</h1>", $tpl->getTemplate());
		$tpl->set("title", "foo");
		$this->assertSame("<h1>foo</h1>", $tpl->parse());
	}

	// public function testBlock()
	// {
	// 	$tpl = new Ste();
	// 	$tpl->setTemplate('<!-- BEGIN block1 --><h1>{title}</h1><!-- END block1 -->');
	// 	$this->assertSame("<h1></h1>", $tpl->parse());
	// 	$tpl->set("title", "foo");
	// 	$this->assertSame("<h1>foo</h1>", $tpl->parse());
	// 	$tpl->hide("block1");
	// 	$this->assertSame("", $tpl->parse());
	// 	$tpl->unhide("block1");
	// 	$this->assertSame("<h1>foo</h1>", $tpl->parse());
	// }

	// public function testArrayParams()
	// {
	// 	$tpl = new Ste();
	// 	$tpl->setTemplate('<h1><a href="{title.href}">{title}</a></h1>');
	// 	$tpl->set("title", "foo");
	// 	$this->assertSame('<h1><a href="">foo</a></h1>', $tpl->parse());
	// 	$tpl->set("title", array("foo", "href" => "/home"));
	// 	$this->assertSame('<h1><a href="/home">foo</a></h1>', $tpl->parse());
	// 	$tpl->set("title", array("bar" => "foo", "href" => "/home"));
	// 	$this->assertSame('<h1><a href="/home">foo</a></h1>', $tpl->parse());

	// 	$tpl->setTemplate('<h1><a href="{title.href}">{title}</a></h1>');
	// 	$tpl->set("title", array("bar" => "foo", "href" => "/home"));
	// 	$this->assertSame('<h1><a href="/home">foo</a></h1>', $tpl->parse());
	// }

	// public function testRealExample()
	// {
	// 	$tpl = new Ste();
	// 	$tpl->load(__DIR__."/index.html");
	// 	$tpl->set("lang", "en");
	// 	$tpl->set("head", array(
	// 		"description" => "STE Template Engine",
	// 		"title" => "STE",
	// 	));
	// 	// no css files
	// 	$tpl->loop("CSS", false);
	// 	$tpl->loop("favicon", false);
	// 	$tpl->loop("items", array(
	// 		"one", "two", "four"
	// 	));
	// 	// include a file as a parameter
	// 	$tpl->set("table_template", "table.html");
	// 	// populates the table similar to the way you'll get the results from the DB
	// 	$tpl->loop("table", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "col2" => "bar"),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));
	// 	// populates second table which has some unavailable cells
	// 	$tpl->loop("table2", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "bigger" => 2),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));

	// 	// check the result, stripping all new lines and tabs for ease implementation of the test
	// 	$this->assertSame(str_replace(array("\n", "\r", "\r\n", "\t"), "", file_get_contents(__DIR__."/result.html")), str_replace(array("\n", "\r", "\r\n", "\t"), "", $tpl->parse()));
	// }

	// public function testRealExampleWithAssign()
	// {
	// 	$tpl = new Ste();
	// 	$tpl->load(__DIR__."/index.html");
	// 	$tpl->assign("lang", "en");
	// 	$tpl->assign("head", array(
	// 		"description" => "STE Template Engine",
	// 		"title" => "STE",
	// 	));
	// 	// no css files
	// 	$tpl->assign("CSS", false);
	// 	$tpl->assign("favicon", false);
	// 	$tpl->assign("items", array(
	// 		"one", "two", "four"
	// 	));
	// 	// include a file as a parameter
	// 	$tpl->assign("table_template", "table.html");
	// 	// populates the table similar to the way you'll get the results from the DB
	// 	$tpl->assign("table", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "col2" => "bar"),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));
	// 	// populates second table which has some unavailable cells
	// 	$tpl->assign("table2", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "bigger" => 2),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));

	// 	// check the result, stripping all new lines and tabs for ease implementation of the test
	// 	$this->assertSame(str_replace(array("\n", "\r", "\r\n", "\t"), "", file_get_contents(__DIR__."/result.html")), str_replace(array("\n", "\r", "\r\n", "\t"), "", $tpl->parse()));
	// }

	// public function testTagSymbols()
	// {
	// 	$tpl = new Ste(array("start_symbol" => "[", "end_symbol" => "]"));
	// 	$tpl->setTemplate("Hi [user]!");
	// 	$tpl->assign("user", "Sebastian");
	// 	$this->assertSame("Hi Sebastian!", $tpl->parse());
	// }

	// public function testTagSymbols2()
	// {
	// 	$tpl = new Ste(array("start_symbol" => "%", "end_symbol" => "%"));
	// 	$tpl->setTemplate("Hi %firstname% %lastname%!");
	// 	$tpl->assign("firstname", "Sebastian");
	// 	$tpl->assign("lastname", "Lebeau");
	// 	$this->assertSame("Hi Sebastian Lebeau!", $tpl->parse());
	// }

	// public function test2TagSymbols()
	// {
	// 	$tpl = new Ste(array("start_symbol" => "{{", "end_symbol" => "}}"));
	// 	$tpl->setTemplate("Hi {{firstname}} {{lastname}}!");
	// 	$tpl->assign("firstname", "Sebastian");
	// 	$tpl->assign("lastname", "Lebeau");
	// 	$this->assertSame("Hi Sebastian Lebeau!", $tpl->parse());
	// }

	// public function test3TagSymbols()
	// {
	// 	$tpl = new Ste(array("start_symbol" => "{{ ", "end_symbol" => " }}"));
	// 	$tpl->setTemplate("Hi {{ firstname }} {{ lastname }}!");
	// 	$tpl->assign("firstname", "Sebastian");
	// 	$tpl->assign("lastname", "Lebeau");
	// 	$this->assertSame("Hi Sebastian Lebeau!", $tpl->parse());
	// }

	// public function testArrayTagSymbols()
	// {
	// 	$tpl = new Ste(array("start_symbol" => "%", "end_symbol" => "%"));
	// 	$tpl->setTemplate("Hi %name.first% %name.last%!");
	// 	$tpl->assign("name", ["first" => "Sebastian", "last" => "Lebeau"]);
	// 	$this->assertSame("Hi Sebastian Lebeau!", $tpl->parse());
	// }

	// public function testFuncTagSymbols()
	// {
	// 	$tpl = new Ste(array("start_symbol" => "[", "end_symbol" => "]"));
	// 	$tpl->registerFunction("year", function () {
	// 		return date("Y");
	// 	});
	// 	$tpl->setTemplate("Happy new [year()] year!");
	// 	$this->assertSame("Happy new ".date("Y")." year!", $tpl->parse());
	// }

	// public function testWithCustomBlocks()
	// {
	// 	$tpl = new Ste(array(
	// 		"start_include" => "{include",
	// 		"end_include" => "}",
	// 		"start_begin_block"  => "{begin",
	// 		"start_end_block"    => "{end",
	// 		"end_block"          => "}",
	// 	));
	// 	$tpl->load(__DIR__."/custom.html");
	// 	$tpl->assign("lang", "en");
	// 	$tpl->assign("head", array(
	// 		"description" => "STE Template Engine",
	// 		"title" => "STE",
	// 	));
	// 	// no css files
	// 	$tpl->assign("CSS", false);
	// 	$tpl->assign("favicon", false);
	// 	$tpl->assign("items", array(
	// 		"one", "two", "four"
	// 	));
	// 	// include a file as a parameter
	// 	$tpl->assign("table_template", "customtable.html");
	// 	// populates the table similar to the way you'll get the results from the DB
	// 	$tpl->assign("table", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "col2" => "bar"),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));
	// 	// populates second table which has some unavailable cells
	// 	$tpl->assign("table2", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "bigger" => 2),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));

	// 	// check the result, stripping all new lines and tabs for ease implementation of the test
	// 	$this->assertSame(str_replace(array("\n", "\r", "\r\n", "\t"), "", file_get_contents(__DIR__."/result.html")), str_replace(array("\n", "\r", "\r\n", "\t"), "", $tpl->parse()));
	// }

	// public function testCustomTags()
	// {
	// 	$tpl = new Ste(array(
	// 		"start_include"      => "%include",
	// 		"end_include"        => "%",
	// 		"start_begin_block"  => "%block",
	// 		"start_end_block"    => "%endblock",
	// 		"end_block"          => "%",
	// 		"start_symbol"       => "%",
	// 		"end_symbol"         => "%"
	// 	));
	// 	$tpl->load(__DIR__."/custom2.html");
	// 	$tpl->assign("lang", "en");
	// 	$tpl->assign("head", array(
	// 		"description" => "STE Template Engine",
	// 		"title" => "STE",
	// 	));
	// 	// no css files
	// 	$tpl->assign("CSS", false);
	// 	$tpl->assign("favicon", false);
	// 	$tpl->assign("items", array(
	// 		"one", "two", "four"
	// 	));
	// 	// include a file as a parameter
	// 	$tpl->assign("table_template", "customtable2.html");
	// 	// populates the table similar to the way you'll get the results from the DB
	// 	$tpl->assign("table", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "col2" => "bar"),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));
	// 	// populates second table which has some unavailable cells
	// 	$tpl->assign("table2", array(
	// 		array("col1" => "one", "col2" => "foo"),
	// 		array("col1" => "two", "bigger" => 2),
	// 		array("col1" => "four", "col2" => "foobar"),
	// 	));

	// 	// check the result, stripping all new lines and tabs for ease implementation of the test
	// 	$this->assertSame(str_replace(array("\n", "\r", "\r\n", "\t"), "", file_get_contents(__DIR__."/result.html")), str_replace(array("\n", "\r", "\r\n", "\t"), "", $tpl->parse()));
	// }
}

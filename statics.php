<?php
	require_once('lib/my/common.php');
	require_once('utils.php');
    require_once "timer.php";

    timer::Start_Timer();

	require_once("HTML/Template/Sigma.php");
	require_once("HTML/Menu.php");
	require_once("HTML/Menu/SigmaRenderer.php");

$blocks_array = array(
	array(	'TITLE' => 'Разделы',
		'CONTENT' => XML2Menu('db/sections.xml',
				'menu.html', 'tree') ),
	array(	'TITLE' => 'Статьи',
		'CONTENT' => XML2Menu('db/articles.xml',
				'menu.html', 'tree') )
);
	$t =& new HTML_Template_Sigma("./themes/$theme", "./cache");
	$t->loadTemplateFile("common.html");

	$renderer =& new stats_SigmaRenderer($t);
	$stats->render($renderer);
	
$t->setCurrentBlock('block');
foreach($blocks_array as $block) {
	$t->setVariable($block);
	$t->parseCurrentBlock();
}

$t->setCurrentBlock('small_block');
$t->setVariable(array( 'TITLE' => 'Друзья',
    'CONTENT' => XML2Menu("db/links.xml", "menu.html", "tree")));
$t->parseCurrentBlock();


if (!empty($errors)) {
	$t->setCurrentBlock("errors");
	$t->setVariable('ERRORS', $errors);
	$t->parseCurrentBlock();
}


if (isset($_GET['id']) && file_exists('db/articles/'.$_GET['id'].'.xml'))
    define('_ARTICLE', $_GET['id']);

if (defined('_ARTICLE')) {
    require_once 'XML/Unserializer.php';

    $unserializer =& new XML_Unserializer(
        array(
            'parseAttributes' => true,
        ));
    $unserializer->unserialize(
        implode(file('db/articles/'._ARTICLE.'.xml'),''), false);
    $data = $unserializer->getUnserializedData();

	$art =& new HTML_Template_Sigma("./themes/$theme", "./cache");
	$art->loadTemplateFile("article.html");

    $pictures = !isset($data['pictures']['img']['src'])
        ? $data['pictures']['img']
        : $data['pictures'];
    foreach($pictures as $pic) {
        $art->setCurrentBlock('pic');
        $art->setVariable(array(
            'BIG'           =>  $pic['big'],
            'ALT'           =>  $pic['alt'],
            'SRC'           =>  $pic['src'],
            'DESCRIPTION'   =>  $pic['description'],
            'PAUTHOR'       =>  $pic['author']
        ));
        $art->parseCurrentBlock();
    }

    $files = !isset($data['files']['file']['src'])
        ? $data['files']['file']
        : $data['files'];
    foreach($files as $file) {
        $art->setCurrentBlock('file');
        $art->setVariable(array(
            'FSRC'           =>  $file['src'],
            'FDESCRIPTION'   =>  $file['description']
        ));
        $art->parseCurrentBlock();
    }

    $art->setCurrentBlock();
    $art->setVariable(array(
        // Article
        'TITLE'          =>  $data['title'],
        'SUBTITLE'       =>  $data['subtitle'],
        'AUTHOR'         =>  $data['author'],
        'ANNOTATION'     =>  $data['annotation'],
        'TEXT'           =>  $data['text']
    ));
    $art->parseCurrentBlock();
}

$t->setVariable(array(
	'CONTENT'=>	!defined('_ARTICLE')
			? XML2Menu('db/articles.xml', 
				'sitemap.html', 'sitemap')
			: $art->get(),
 	'TITLE'	=>  'Чтиво',
    'TIME' 	=>	timer::Get_Time()
));
$t->parseCurrentBlock();
$t->show();

?>

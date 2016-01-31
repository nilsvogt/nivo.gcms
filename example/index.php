<!doctype html>
<?PHP
	require('../src/gcms.class.php');

	$gcms = new Nivo\GCMS();

	// Assign A Custom Storage Folder For The Cached Contents
	$gcms->storage = __DIR__ . "/storage/gcms/";

	// Get The Contents Of The Two Worksheets
	$sheet1 = $gcms->getContents('cachefile-sheet1.txt', '1uZY1LNd4id-l8DElqVMQoam24HK1rGDwKXZEfr_0FbI', 0);
	$sheet2 = $gcms->getContents('cachefile-sheet2.txt', '1uZY1LNd4id-l8DElqVMQoam24HK1rGDwKXZEfr_0FbI', 134327007);
?>
<html>
	<meta charset="utf-8" />
	<title>nivo gcms example</title>
</html>
<body>
	<p><?= $sheet1['intro'] ?></p>
	<pre><?= $sheet1['howto'] ?></pre>

	<h2><?= $sheet1['headline'] ?></h2>
	<p><?= $sheet1['copy'] ?></p>

	<section>
		<h2><?= $sheet1['section']['unorderedList']['headline'] ?></h2>
		<ul>
			<?PHP foreach($sheet1['section']['unorderedList']['someList'] as $row): ?>
				<li><?= $row ?></li>
			<?PHP endforeach ?>
		</ul>
	</section>

	<section>
		<h2><?= $sheet1['section']['definitionList']['headline'] ?></h2>
		<dl>
			<?PHP foreach($sheet1['section']['definitionList']['list'] as $title => $value): ?>
				<dt><?= $title ?></dt>
				<dd><?= $value ?></dd>
			<?PHP endforeach ?>
		</dl>
	</section>

	<hr />

	<section>
		<h2><?= $sheet2['headline'] ?></h2>
		<p><?= $sheet2['copy'] ?></p>
	</section>
</body>
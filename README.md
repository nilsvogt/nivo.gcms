GCMS
====

You can utilize GCMS to store all your strings in a well organized Google Speadsheet. This not only keeps your templates from getting messed by too much contents but also enables others to easily manage the contents of your project.

[Here](https://docs.google.com/spreadsheets/d/1uZY1LNd4id-l8DElqVMQoam24HK1rGDwKXZEfr_0FbI/edit#gid=0) you can find the Spreadsheet used for the example.

How to
------

```
	require('../src/gcms.class.php');

	$gcms = new Nivo\GCMS();

	// Assign A Custom Storage Folder For The Cached Contents
	$gcms->storage = __DIR__ . "/storage/gcms/";

	// Get The Contents Of The Two Worksheets
	$sheet1 = $gcms->getContents('cachefile-sheet1.txt', '1uZY1LNd4id-l8DElqVMQoam24HK1rGDwKXZEfr_0FbI', 0);
	$sheet2 = $gcms->getContents('cachefile-sheet2.txt', '1uZY1LNd4id-l8DElqVMQoam24HK1rGDwKXZEfr_0FbI', 134327007);"
```

- ```$sheet1``` now contains all contents assigned in the **first** worksheet.
- ```$sheet2``` now contains all contents assigned in the **second** worksheet.

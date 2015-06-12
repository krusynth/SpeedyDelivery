<?php

$user = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if($user)
{

	$filename = 'http://tinyletter.com/'.$user.'/archive';

	$listHtml = file_get_contents($filename);

	if($listHtml)
	{
		print '<?xml version="1.0" encoding="utf-8"?><rss version="2.0"><channel>';

		$listDom = new DOMDocument;
		$listDom->loadHTML($listHtml);

		$listXpath = new DomXpath($listDom);

		$title = $listXpath->query('//*[@class="subject"]')->item(0)->nodeValue;
		$byLine = $listXpath->query('//*[@class="by-line"]')->item(0)->nodeValue;

		print '<title>' . $title . '</title>';
		print '<link>' . $filename . '</link>';
		print '<description>' . $byLine . '</description>';
		// print '<lastBuildDate></lastBuildDate>';
		print '<language>en-us</language>';

		$messageLinks = $listXpath->query('//*[@class="message-item"]/a');

		foreach($messageLinks as $messageLink)
		{
			print '<item>';

			$url = $messageLink->getAttribute('href');

			$messageHtml = file_get_contents($url);

			$messageDom = new DOMDocument;
			$messageDom->loadHTML($messageHtml);

			$messageXpath = new DomXpath($messageDom);

			$title = $messageXpath->query('//*[@class="subject"]')->item(0)->nodeValue;
			$byLine = $messageXpath->query('//*[@class="by-line"]')->item(0)->nodeValue;
			$date = $messageXpath->query('//*[@class="date"]')->item(0)->nodeValue;
			$content = $messageDom->saveXML(
				$messageXpath->query('//*[@class="message-body"]')->item(0)
			);

			print '<title>' . $title . '</title>';
			print '<link>' . $url . '</link>';
			print '<guid>' . $url . '</guid>';
			print '<pubDate>' . date('r', strtotime($date)) . '</pubDate>';
			print '<description><![CDATA[ ' . $content . ' ]]></description>';
			print '</item>';
		}

		print '</channel></rss>';
	}
	else
	{
		header("HTTP/1.0 404 Not Found");
		print "There is no archive for user <strong>$user</strong>";
	}
}
else
{
	print '<html>
<head><title>Generate SpeedyDelivery RSS</title></head>
<body>
<h1>Generate SpeedyDelivery RSS</h1>
<p>Enter the user\'s name below, then copy the resulting url to use in your RSS reader.</p>
<form>
	<input type="text" name="user" />
	<input type="submit" value="Go">
</form>
</body>
</html>';
}

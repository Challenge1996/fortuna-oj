<?php

class Text extends CI_Model
{
	function bb2html($bbtext)
	{
		
		$bbtext = htmlentities($bbtext);

		$bbtags = array(
				'[heading1]' => '<h1>','[/heading1]' => '</h1>',
				'[heading2]' => '<h2>','[/heading2]' => '</h2>',
				'[heading3]' => '<h3>','[/heading3]' => '</h3>',
				'[h1]' => '<h1>','[/h1]' => '</h1>',
				'[h2]' => '<h2>','[/h2]' => '</h2>',
				'[h3]' => '<h3>','[/h3]' => '</h3>',

				'[paragraph]' => '<p>','[/paragraph]' => '</p>',
				'[para]' => '<p>','[/para]' => '</p>',
				'[p]' => '<p>','[/p]' => '</p>',
				'[left]' => '<p style="text-align:left;">','[/left]' => '</p>',
				'[right]' => '<p style="text-align:right;">','[/right]' => '</p>',
				'[center]' => '<p style="text-align:center;">','[/center]' => '</p>',
				'[justify]' => '<p style="text-align:justify;">','[/justify]' => '</p>',

				'[bold]' => '<span style="font-weight:bold;">','[/bold]' => '</span>',
				'[italic]' => '<span style="font-weight:bold;">','[/italic]' => '</span>',
				'[underline]' => '<span style="text-decoration:underline;">','[/underline]' => '</span>',
				'[b]' => '<span style="font-weight:bold;">','[/b]' => '</span>',
				'[i]' => '<span style="font-style:italic;">','[/i]' => '</span>',
				'[u]' => '<span style="text-decoration:underline;">','[/u]' => '</span>',
				'[break]' => '<br>',
				'[br]' => '<br>',
				'[newline]' => '<br>',
				'[nl]' => '<br>',

				'[unordered_list]' => '<ul>','[/unordered_list]' => '</ul>',
				'[list]' => '<ul>','[/list]' => '</ul>',
				'[ul]' => '<ul>','[/ul]' => '</ul>',

				'[ordered_list]' => '<ol>','[/ordered_list]' => '</ol>',
				'[ol]' => '<ol>','[/ol]' => '</ol>',
				'[list_item]' => '<li>','[/list_item]' => '</li>',
				'[li]' => '<li>','[/li]' => '</li>',

				'[*]' => '<li>','[/*]' => '</li>',
				'[code]' => '<code>','[/code]' => '</code>',
				'[preformatted]' => '<pre>','[/preformatted]' => '</pre>',
				'[pre]' => '<pre>','[/pre]' => '</pre>',

				'[quote]' => '<blockquote>','[/quote]' => '</blockquote>',

				// '[' and ']' are escaped in the form, then '&' in the entity is escaped again, we need to restore them
				'&amp;#91;' => '[', '&amp;#93;' => ']'
		);

		$bbextended = array(
			"/\[url\](.*?)\[\/url\]/is" => "<a href=\"http://$1\" title=\"$1\">$1</a>",
			"/\[url=(.*?)\](.*?)\[\/url\]/is" => "<a href=\"$1\" title=\"$1\">$2</a>",
			"/\[email=(.*?)\](.*?)\[\/email\]/is" => "<a href=\"mailto:$1\">$2</a>",
			"/\[mail=(.*?)\](.*?)\[\/mail\]/is" => "<a href=\"mailto:$1\">$2</a>",
			"/\[img\](.*?)\[\/img\]/is" => "<img src=\"$1\" alt=\" \" />",
			"/\[image\](.*?)\[\/image\]/is" => "<img src=\"$1\" alt=\" \" />",
			"/\[image_left\](.*?)\[\/image_left\]/is" => "<img src=\"$1\" alt=\" \" class=\"img_left\" />",
			"/\[image_right\](.*?)\[\/image_right\]/is" => "<img src=\"$1\" alt=\" \" class=\"img_right\" />",
			"/\[list=1\](.*?)\[\/list\]/is" => "<ol>$1</ol>",
			"/\[color=(.*?)\](.*?)\[\/color\]/is" => "<span style=\"color:$1\">$2</span>"
		);

		foreach($bbextended as $match=>$replacement)
			$bbtext = preg_replace($match, $replacement, $bbtext);
		$bbtext = str_ireplace(array_keys($bbtags), array_values($bbtags), $bbtext); // place this after extended because of "[list=1]"
		return $bbtext;
	}
}


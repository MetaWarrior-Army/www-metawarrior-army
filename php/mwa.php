<?php
require 'config.php';

# Generate Nonce for Auth
function generateNonce($n)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}

function getFeed($feed_url) {
	$content = file_get_contents($feed_url);
	$x = new SimpleXmlElement($content);
	echo "<div class=\"list-group list-group-dark\">";
    $i = 0;
	foreach($x->channel->item as $entry) {
        // format pubDate
        $time = strtotime($entry->pubDate);
        $newformat = date('M d, Y',$time);
        
        echo "<a class=\"list-group-item list-group-item-action list-group-item-dark\" href='$entry->link' title='$entry->title'>" . $entry->title . "</a>";
        echo "<div class=\"d-flex w-100 justify-content-right\">";
        echo "<small class=\"text-secondary\">".$newformat."</small>";
        echo "</div>";
        $i++;
        if($i > 4){
            break;
        }
	}
	echo "</div>";
}



?>
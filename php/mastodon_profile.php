<?php
// Check mastodon to see if they follow 1 account
// get mastodon account ID and followers
// curl -X GET -H 'Authorization: Bearer $MASTODON_API_TOKEN' https://mastodon.metawarrior.army/api/v1/accounts/lookup?acct=admin@mastodon.metawarrior.army

$header = ['Authorization: Bearer '.$MASTODON_API_TOKEN];
$crl = curl_init('https://mastodon.metawarrior.army/api/v1/accounts/lookup?acct='.$userObj->username.'@mastodon.metawarrior.army');
//OIDC User Info
//$crl = curl_init("https://auth.metawarrior.army/userinfo");
curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
try{
    $get_usr_resp = curl_exec($crl);
    curl_close($crl);
    //var_dump($get_usr_resp);
}
catch(Exception $e){
    echo "Caught exception: ", $e->getMessage();
}

$user_obj = json_decode($get_usr_resp);

//var_dump($user_obj);

echo "<img class=\"rounded\" width=\"50px\" src=\"".$user_obj->avatar_static."\">";
echo "<a href=\"".$user_obj->url."\" target=\"_blank\" class=\"link-light\"><h5>".$user_obj->username."@metawarrior.army</h5></a>";
echo "<p class=\"small\">Followers: <b>".$user_obj->followers_count."</b></p>";
echo "<p class=\"small\">Following: <b>".$user_obj->following_count."</b></p>";
echo "<p class=\"small\">Toots: <b>".$user_obj->statuses_count."</b></p>";










?>
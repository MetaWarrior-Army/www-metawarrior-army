<?php

// Check roundcube database table 'users' field 'username' for user@metawarrior.army.
// This should prove that they logged into email
// SELECT * FROM users;
// user_id,username,mail_host,created,last_login,...


// Check mastodon to see if they follow 1 account
// get mastodon account ID and followers
// curl -X GET -H 'Authorization: Bearer $MASTODON_API_TOKEN' https://mastodon.metawarrior.army/api/v1/accounts/lookup?acct=admin@mastodon.metawarrior.army

// Check mastodon to see if they posted something
// curl -X GET -H 'Authorization: Bearer $MASTODON_API_TOKEN' https://mastodon.metawarrior.army/api/v1/accounts/111649245053649240/statuses

// Check Discourse to see if they logged in and posted something
// curl -X GET "https://discourse.metawarrior.army/u/tazerface.json" -H "Api-Key: $DISCOURSE_API_TOKEN" -H "Api-Username: system" 

// Check to see if they've logged into Matrix
// synapse_user@synapse:10.124.0.7
// SELECT * FROM profiles
// user_id,displayname,avatar_url



?>
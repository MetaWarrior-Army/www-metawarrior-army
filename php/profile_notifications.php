<?php
    $SHOW_NOTIFICATIONS = false;
    $NOTIFICATIONS = array();
 
    if($userObj->username){
        if($userObj->nft_0_tx){
        }
        else{
            array_push($NOTIFICATIONS,"<br><p class=\"lead\">You still have to <a href=\"https://nft.metawarrior.army/\" class=\"link-light\">Mint</a> your NFT!</p>");
            $SHOW_NOTIFICATIONS = true;
        }
    }
    else{
        array_push($NOTIFICATIONS,"<br><p class=\"lead\">Welcome to MetaWarrior Army. <br>To become a Member you must choose a username and <b><a href=\"https://nft.metawarrior.army\" class=\"link-info\">Mint your NFT</a></b>.</p>");
        $SHOW_NOTIFICATIONS = true;
    }

    // Get invite code and referral count
    if($userObj->invite_code){
        $user_invite_code = $userObj->invite_code;
        array_push($NOTIFICATIONS,"<br><p class=\"lead\">Here's your personal invite code to share with friends: <span class=\"text-info\"><b><a id=\"invite_code\" class=\"link-info\" href=\"https://nft.metawarrior.army/?invite=".$user_invite_code."\">".$user_invite_code."</a></b></span> <span class=\"p-5\" onclick=\"copyInvite()\"><i class=\"fa fa-clone\"></i></span></p>");
        $SHOW_NOTIFICATIONS = true;
    }
    if($userObj->num_referrals){
        if($userObj->num_referrals > 0){
            array_push($NOTIFICATIONS,"<br><p class=\"lead\">You've referred <span class=\"text-info\"><b>".$userObj->num_referrals."</b></span> new members. Congrats! Stay tuned for your rewards!</p>");
        }
    }

?>
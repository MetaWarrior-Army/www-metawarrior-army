<?php
    $SHOW_NOTIFICATIONS = false;
    $NOTIFICATIONS = array();
 
    if($userObj->username){
        if($userObj->nft_0_tx){
        }
        else{
            array_push($NOTIFICATIONS,"<br>You still have to <a href=\"https://nft.metawarrior.army/\" class=\"link-light\">Mint</a> your NFT!");
            $SHOW_NOTIFICATIONS = true;
        }
    }
    else{
        array_push($NOTIFICATIONS,"<br>Welcome to MetaWarrior Army. <br>To become a Member you must choose a username and <b><a href=\"https://nft.metawarrior.army\" class=\"link-info\">Mint your NFT</a></b>.");
        $SHOW_NOTIFICATIONS = true;
    }

?>
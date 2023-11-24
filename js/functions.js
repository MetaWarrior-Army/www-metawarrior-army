// SCREEN USERNAMES
// Later we need to check a DB of usernames
import { blocked_users } from "./blockedusers.js";
const min_username_length = 5;
const max_username_length = 20;
// For cleaning username input
const word_re = /^\w+$/;


export function screenUsername(event) {
    const error_msg = document.getElementById('usernameFormError');
    const button = document.getElementById('submit');

    if(blocked_users.includes(event.target.value)){
        console.log("Blocked Username");
        // Blocked username
        error_msg.innerText = "Username is taken.";
        button.disabled = true;
    }
    else if(event.target.value.length < min_username_length){
        // Empty username
        error_msg.innerText = "Username is too short.";
        button.disabled = true;
    }
    else if(!word_re.exec(event.target.value)){
        // Non word character
        error_msg.innerText = "Invalid character in username.";
        button.disabled = true;
    }
    else if(event.target.value.length > max_username_length){
        // too long
        error_msg.innerText = "Username too long.";
        button.disabled = true;
    }
    else{
        error_msg.innerText = "";
        button.disabled = false;
    }
};



//console.log("profile.js");

function copyInvite() {
    
    var code_link = document.getElementById("invite_code").href;
    //console.log(code_link);
    navigator.clipboard.writeText(code_link);
    alert("Invite link copied.");
}

function closeNotifications() {
    console.log("closing notifications");
    var not = document.getElementById('notifications');
    not.style.display = 'none';
}
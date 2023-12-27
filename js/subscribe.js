var button = document.getElementById('submit');
var email_input = document.getElementById('email1');
var check = document.getElementById('check1');

function checkEmail(input) {
    if(input){
        // Check if email is valid
        return true;
    }
    else {
        return false;
    }
}

function isChecked() {
    if(check.checked){
        return true;
    }
    else{
        return false;
    }
}

function checkForm(){
    if(isChecked()){
        if(checkEmail(email_input.value)){
            button.disabled = false;
        }
        else{
            button.disabled = true;
        }
    }
    else{
        button.disabled = true;
    }
}

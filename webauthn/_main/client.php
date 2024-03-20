<?php

require 'php/mwa.php';

$address = '0x7B93AED8239f85949FDCec66f1f9ED13a5026adD';

// tiger192,4 is 32 rounds, we base64encode it as a string for the server API
$hashedAddress = base64_encode(hash('tiger192,4', $address, true));

// Start server session
session_start();

// This will validate our session or redirect our user to login
// This page depends on an active session. We prefer the access_token for getting the /userinfo from the OAuth server
if(!isset($_SESSION['access_token']) or isset($_SESSION['access_token']->error)){
  // error_log("NO ACCESS TOKEN");
  // we don't have an access_token
  // do we have what we need to get an access_token?
  header('Location: /login');
  return;
}
if(!isset($_SESSION['userinfo']->username)){
    // Userinfo for address and validating token
    $header = ['Authorization: Bearer '.$_SESSION['access_token']->access_token, 'Content-type: application/json'];
    $crl = curl_init($OAUTH_USERINFO_ENDPOINT);
    curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
    curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
    try{
      $userinfo_resp = curl_exec($crl);
      $userinfo = json_decode($userinfo_resp);
      curl_close($crl);
    }
    catch(Exception $e){
      echo "Caught exception: ", $e->getMessage();
    }
  
    // Get user's current obj from api
    if(isset($userinfo)){
      // Use access_token to get user information
      $getUserPost = [
        'address' => $userinfo->sub,
      ];
      $header = ['Authorization: Bearer '.$_SESSION['access_token']->access_token, 'Content-type: application/json'];
      $crl = curl_init($GET_USER_API_URL);
      //OIDC User Info
      //$crl = curl_init("https://auth.metawarrior.army/userinfo");
      curl_setopt($crl, CURLOPT_HTTPHEADER,$header);
      curl_setopt($crl, CURLOPT_POST,true);
      curl_setopt($crl, CURLOPT_POSTFIELDS,json_encode($getUserPost));
      curl_setopt($crl,CURLOPT_RETURNTRANSFER, true);
      try{
        $get_usr_resp = curl_exec($crl);
        curl_close($crl);
      }
      catch(Exception $e){
        echo "Caught exception: ", $e->getMessage();
      }
  
      $userObj = json_decode($get_usr_resp);
  
      $_SESSION['userinfo'] = $userObj;
    }
}
else{
    $userObj = $_SESSION['userinfo'];
}

if(!isset($userObj->username)){
    header("Location: /finish_mint");
    return;
}
elseif(!isset($userObj->nft_0_tx)) {
    header('Location: /finish_mint?username='.$userObj->username);
    return;
}

// Okay we should have everything we need to know about the user at this point

// Hash the user's address for transfer across the wire. base64 encode it for database storage
// tiger192,4 is 32 rounds, we base64encode it as a string for the server API
// We use this later in the javascript below
if(isset($userObj->address)){
    $hashedAddress = base64_encode(hash('tiger192,4', sprintf($userObj->address), true));
}
else{
    error_log("Undefined address");
    header('Location: /login');
    return;
}



?>


<!DOCTYPE html>
<!-- 
MetaWarrior Army WebAuthn Client
Borrowed from https://github.com/lbuchs/WebAuthn
Author: admin@metawarrior.army
-->
<html>
    <head>
        <title>MetaWarrior Army - WebAuthn</title>
        <meta charset="UTF-8">
        <!-- Font Awseome -->
        <script src="https://kit.fontawesome.com/041b1cce3a.js" crossorigin="anonymous"></script>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <!-- favicon -->
        <link rel="icon" type="image/x-icon" href="/media/img/logo.ico"></link>
        <script>

        const address_hashed = '<?php echo $hashedAddress; ?>';
        //console.log(address_hashed)

        /**
         * creates a new FIDO2 registration
         * @returns {undefined}
         */
        async function createRegistration() {
            try {

                // check browser support
                if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                    throw new Error('Browser not supported.');
                }

                // get create args
                let rep = await window.fetch('server.php?fn=getCreateArgs' + getGetParams(), {method:'GET', cache:'no-cache'});
                const createArgs = await rep.json();

                // error handling
                if (createArgs.success === false) {
                    throw new Error(createArgs.msg || 'unknown error occured');
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                recursiveBase64StrToArrayBuffer(createArgs);

                // create credentials
                const cred = await navigator.credentials.create(createArgs);

                // create object
                const authenticatorAttestationResponse = {
                    transports: cred.response.getTransports  ? cred.response.getTransports() : null,
                    clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
                };

                // check auth on server side
                rep = await window.fetch('server.php?fn=processCreate' + getGetParams(), {
                    method  : 'POST',
                    body    : JSON.stringify(authenticatorAttestationResponse),
                    cache   : 'no-cache'
                });
                const authenticatorAttestationServerResponse = await rep.json();

                // prompt server response
                if (authenticatorAttestationServerResponse.success) {
                    reloadServerPreview();
                    window.alert(authenticatorAttestationServerResponse.msg || 'registration success');

                } else {
                    throw new Error(authenticatorAttestationServerResponse.msg);
                }

            } catch (err) {
                reloadServerPreview();
                window.alert(err.message || 'unknown error occured');
            }
        }


        /**
         * checks a FIDO2 registration
         * @returns {undefined}
         */
        async function checkRegistration() {
            try {

                if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                    throw new Error('Browser not supported.');
                }

                // get check args
                let rep = await window.fetch('server.php?fn=getGetArgs' + getGetParams(), {method:'GET',cache:'no-cache'});
                const getArgs = await rep.json();

                // error handling
                if (getArgs.success === false) {
                    throw new Error(getArgs.msg);
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                recursiveBase64StrToArrayBuffer(getArgs);

                // check credentials with hardware
                const cred = await navigator.credentials.get(getArgs);

                // create object for transmission to server
                const authenticatorAttestationResponse = {
                    id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
                    clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
                    signature: cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null,
                    userHandle: cred.response.userHandle ? arrayBufferToBase64(cred.response.userHandle) : null
                };

                // send to server
                rep = await window.fetch('server.php?fn=processGet' + getGetParams(), {
                    method:'POST',
                    body: JSON.stringify(authenticatorAttestationResponse),
                    cache:'no-cache'
                });
                const authenticatorAttestationServerResponse = await rep.json();

                // check server response
                if (authenticatorAttestationServerResponse.success) {
                    reloadServerPreview();
                    window.alert(authenticatorAttestationServerResponse.msg || 'Login success!');
                } else {
                    throw new Error(authenticatorAttestationServerResponse.msg);
                }

            } catch (err) {
                reloadServerPreview();
                window.alert(err.message || 'unknown error occured');
            }
        }

        function clearRegistration() {
            window.fetch('server.php?fn=clearRegistrations' + getGetParams(), {method:'GET',cache:'no-cache'}).then(function(response) {
                return response.json();

            }).then(function(json) {
               if (json.success) {
                   reloadServerPreview();
                   window.alert(json.msg);
               } else {
                   throw new Error(json.msg);
               }
            }).catch(function(err) {
                reloadServerPreview();
                window.alert(err.message || 'unknown error occured');
            });
        }


        function queryFidoMetaDataService() {
            window.fetch('server.php?fn=queryFidoMetaDataService' + getGetParams(), {method:'GET',cache:'no-cache'}).then(function(response) {
                return response.json();

            }).then(function(json) {
               if (json.success) {
                   window.alert(json.msg);
               } else {
                   throw new Error(json.msg);
               }
            }).catch(function(err) {
                window.alert(err.message || 'unknown error occured');
            });
        }

        /**
         * convert RFC 1342-like base64 strings to array buffer
         * @param {mixed} obj
         * @returns {undefined}
         */
        function recursiveBase64StrToArrayBuffer(obj) {
            let prefix = '=?BINARY?B?';
            let suffix = '?=';
            if (typeof obj === 'object') {
                for (let key in obj) {
                    if (typeof obj[key] === 'string') {
                        let str = obj[key];
                        if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                            str = str.substring(prefix.length, str.length - suffix.length);

                            let binary_string = window.atob(str);
                            let len = binary_string.length;
                            let bytes = new Uint8Array(len);
                            for (let i = 0; i < len; i++)        {
                                bytes[i] = binary_string.charCodeAt(i);
                            }
                            obj[key] = bytes.buffer;
                        }
                    } else {
                        recursiveBase64StrToArrayBuffer(obj[key]);
                    }
                }
            }
        }

        /**
         * Convert a ArrayBuffer to Base64
         * @param {ArrayBuffer} buffer
         * @returns {String}
         */
        function arrayBufferToBase64(buffer) {
            let binary = '';
            let bytes = new Uint8Array(buffer);
            let len = bytes.byteLength;
            for (let i = 0; i < len; i++) {
                binary += String.fromCharCode( bytes[ i ] );
            }
            return window.btoa(binary);
        }

        /**
         * Get URL parameter
         * @returns {String}
         */
        function getGetParams() {
            let url = '';

            url += '&apple=' + ('0');
            url += '&yubico=' + ('0');
            url += '&solo=' + ('0');
            url += '&hypersecu=' + ('0');
            url += '&google=' + ('0');
            url += '&microsoft=' + ('0');
            url += '&mds=' + ('0');

            url += '&requireResidentKey=' + ('0');

            url += '&type_usb=' + ('1');
            url += '&type_nfc=' + ('1');
            url += '&type_ble=' + ('1');
            url += '&type_int=' + ('1');
            url += '&type_hybrid=' + ('1');

            url += '&fmt_android-key=' + ('0');
            url += '&fmt_android-safetynet=' + ('0');
            url += '&fmt_apple=' + ('0');
            url += '&fmt_fido-u2f=' + ('0');
            url += '&fmt_none=' + ('1');
            url += '&fmt_packed=' + ('0');
            url += '&fmt_tpm=' + ('0');

            url += '&rpId=' + encodeURIComponent('www.metawarrior.army');

            url += '&userId=' + encodeURIComponent(address_hashed);
            url += '&userName=' + encodeURIComponent('rodent');
            url += '&userDisplayName=' + encodeURIComponent('rodent');

            url += '&userVerification=discouraged';

            return url;
        }

        function reloadServerPreview() {
            let iframe = document.getElementById('serverPreview');
            iframe.src = iframe.src;
        }

        function setAttestation(attestation) {
            let inputEls = document.getElementsByTagName('input');
            for (const inputEl of inputEls) {
                if (inputEl.id && inputEl.id.match(/^(fmt|cert)\_/)) {
                    inputEl.disabled = !attestation;
                }
                if (inputEl.id && inputEl.id.match(/^fmt\_/)) {
                    inputEl.checked = attestation ? inputEl.id !== 'fmt_none' : inputEl.id === 'fmt_none';
                }
                if (inputEl.id && inputEl.id.match(/^cert\_/)) {
                    inputEl.checked = attestation ? inputEl.id === 'cert_mds' : false;
                }
            }
        }

        /**
         * force https on load
         * @returns {undefined}
         */
        window.onload = function() {
            if (location.protocol !== 'https:' && location.host !== 'localhost') {
                location.href = location.href.replace('http', 'https');
            }

            /*
            if (!document.getElementById('attestation_yes').checked) {
                setAttestation(false);
            }
            */
        }

        </script>
        <style>
            .splitter {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                margin: 0;
                padding: 0;
            }
            .splitter > .form {
                flex: 1;
                min-width: 600px;
            }
            .splitter > .serverPreview {
                width: 1024px;
                min-height: 700px;
                margin: 0;
                padding: 0;
                border: 0px solid grey;
                display: flex;
                flex-direction: column;
            }
            .splitter > .serverPreview iframe {
                width: 1024px;;
                flex: 1;
                border: 0;
            }
        </style>
    </head>
    
    <body class="d-flex h-100 text-center text-bg-dark">
    
        <div class="container d-flex w-100 h-100 p-3 mx-auto flex-column">
    
            <header class="mb-auto">
                <div>
                    <h3 class="float-md-start"><a href="/"><img src="/media/img/mwa_logo0.png" width="300px" class="img-fluid p-3"></a></h3>        
                    <nav class="navbar navbar-expand-lg navbar-dark bg-dark float-md-end">
                        <div class="container-fluid">
                        <a class="navbar-brand" href="/profile">
                        <?php
                            if($userObj->nft_0_tx){
                                echo "<img width=\"25px\" class=\"rounded\" src=\"https://nft.metawarrior.army/avatars/".$userObj->address.".png\" /></center>";
                            }
                            else{
                                echo '<img src="/media/img/icon.png" width="32px"></a>';

                            }
                        ?>    
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item dropdown">
                                <a class="nav-link active dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Profile
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark text-light bg-dark" aria-labelledby="navbarDropdown">

                                <?php 
                                    //var_dump($_SESSION['userinfo']);

                                    if($userObj->nft_0_tx){
                                        include('../../php/usermenu.php');
                                    }
                                        
                                ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="icon-square p-2 d-inline-flex align-items-center justify-content-center fs-8 flex-shrink-0 me-3">
                                    <span class="p-1 text-warning"><i class="p-1 fa-solid fa-key" aria-hidden="true"></i></span>
                                    <a class="dropdown-item text-light" href="/webauthn/_main/client">2FA Keys</a>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon-square p-2 d-inline-flex align-items-center justify-content-center fs-8 flex-shrink-0 me-3">
                                    <span class="p-1"><i class="p-1 fa fa-sign-out" aria-hidden="true"></i></span>
                                    <a class="dropdown-item text-light" href="<?php echo $oauth_logout_url; ?>">Log out</a>
                                    </div>
                                </li>
                                </ul>
                            </li>
                            <!-- Disabled -->
                            <!--
                            <li class="nav-item">
                                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
                            </li>
                            -->
                            </ul>
                        </div>
                        </div>
                    </nav>
                </div>

            </header>

            <main class="px-3 mb-5">
                <div class="container px-4 py-5 rounded-3 shadow-lg" id="featured-3">
                    <!--
                    <div class="overflow-hidden mb-5" style="max-height: 30vh;">
                        <img class="img-fluid rounded-3 border shadow-lg mb-4" src="/media/img/mission_banner.png" width="1080">
                    </div>
                    -->
                    <h1 class="display-1 mb-5">🔑</h1>
                    <h1 class="display-5 fw-bold">Add/Manage Security Keys</h1>
                    <p class="lead border-bottom p-3">Use a physical security key or password manager that supports passkeys to enable 2nd Factor Authentication on your account.</p>
                    <button onclick="createRegistration()" class="btn btn-lg btn-outline-info fw-bold">Register New Key</button>
                    <button onclick="checkRegistration()" class="btn btn-lg btn-outline-info fw-bold">Check Key</button>
                    <button onclick="clearRegistration()" class="btn btn-lg btn-outline-warning fw-bold">Delete All Keys</button>
                    <div class="justify-content-center row mt-5 g-4 py-5 row-cols-1 row-cols-lg-3">
                        <div class="w-100 justify-content-center splitter">
                            <p class="small">Keys registered to <span class="text-info"><i><?php echo $userObj->address; ?>:</i></span></p>
                            <div class="serverPreview">
                                <iframe src="server.php?fn=getStoredDataHtml&userId=<?php echo $hashedAddress; ?>" id="serverPreview"></iframe>
                            </div>
                        </div>
                        <!--
                        <div class="feature col">
                            <div class="feature-icon d-inline-flex align-items-center justify-content-center fs-2 mb-3">
                                <h1 class="display-4">✉️</h1>
                            </div>
                            <h3 class="fs-2">Email</h3>
                            <p>Full service email platform with encrypted storage, spam filtering, 1 GB of space, folder rules and more.</p>
                        </div>

                        <div class="feature col">
                            <div class="feature-icon d-inline-flex align-items-center justify-content-center fs-2 mb-3">
                                <h1 class="display-4">📢</h1>
                            </div>
                            <h3 class="fs-2">Social Media</h3>
                            <p>MetaWarrior Army owned Mastodon server on the global ActivityPub network with over 10 Million users.</p>
                        </div>

                        <div class="feature col">
                            <div class="feature-icon d-inline-flex align-items-center justify-content-center fs-2 mb-3">
                                <h1 class="display-4">💬</h1>
                            </div>
                            <h3 class="fs-2">Chat, Voice & Video</h3>
                            <p>Encrypted chat, voice, and video calls using the Matrix open standard supporting encryption, groups, rooms, and spaces.</p>
                        </div>

                        <div class="feature col">
                            <div class="feature-icon d-inline-flex align-items-center justify-content-center fs-2 mb-3">
                                <h1 class="display-4">📝</h1>
                            </div>
                            <h3 class="fs-2">Discussion Forums</h3>
                            <p>Open Source forums for long form discussions and threads supporting reply-by email.</p>
                        </div>
                        -->

                    </div>
                </div>

                <div class="b-example-divider"></div>


                <!-- Old client.php UI

                <h1 style="margin: 40px 10px 2px 0;">MetaWarrior Army WebAuthn Client</h1>
                <div style="font-style: italic;">Register and check w/ server preview.</div>
                <div class="splitter">
                    <div class="form">
                        <div>&nbsp;</div>
                        <div>&nbsp;</div>
                        <div>Dev page for <a href="https://github.com/lbuchs/WebAuthn">MetaWarrior Army</a> WebAuthn client.</div>
                        <div>
                            <div>&nbsp;</div>
                            <table>
                                <tbody><tr>
                                        <td>
                                            <button type="button" onclick="createRegistration()">&#10133; new registration</button>
                                        </td>
                                        <td>
                                            <button type="button" onclick="checkRegistration()">&#10068; check registration</button>
                                        </td>
                                        <td>
                                            <button type="button" onclick="clearRegistration()">&#9249; clear all registrations</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div>&nbsp;</div>   

                        </div>
                    </div>
                    <div class="serverPreview">
                        <p style="margin-left:10px;font-weight: bold;">Here you can see what's saved on the server:</p>
                        <iframe src="server.php?fn=getStoredDataHtml&userId=" id="serverPreview"></iframe>
                    </div>
                <div>
                -->
            </main>

        </div>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    </body>
</html>

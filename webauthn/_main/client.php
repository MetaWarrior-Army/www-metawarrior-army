<?php

require 'php/mwa.php';

$address = '0x7B93AED8239f85949FDCec66f1f9ED13a5026adD';

// tiger192,4 is 32 rounds, we base64encode it as a string for the server API
$hashedAddress = base64_encode(hash('tiger192,4', $address, true));


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
        <script>

        const address_hashed = '<?php echo $hashedAddress; ?>';

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
                    window.alert(authenticatorAttestationServerResponse.msg || 'login success');
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
            body {
                font-family:sans-serif;
                margin: 0 20px;
                padding: 0;
            }
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
                width: 740px;
                min-height: 700px;
                margin: 0;
                padding: 0;
                border: 1px solid grey;
                display: flex;
                flex-direction: column;
            }

            .splitter > .serverPreview iframe {
                width: 700px;
                flex: 1;
                border: 0;
            }

        </style>
    </head>
    <body>
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
                <iframe src="server.php?fn=getStoredDataHtml&userId=<?php echo $hashedAddress; ?>" id="serverPreview"></iframe>
            </div>
        <div>
    </body>
</html>

<?php

// MetaWarrior Army WebAuthn Server
// Modified w/ attribution below
// Author: admin@metawarrior.army
// Basic FIDO/U2F WebAuthn implementation in PHP
// Storage to pgsql database

/*
 * Copyright (C) 2022 Lukas Buchs
 * license https://github.com/lbuchs/WebAuthn/blob/master/LICENSE MIT
 *
 * Server test script for WebAuthn library. Saves new registrations in session.
 *
 *            JAVASCRIPT            |          SERVER
 * ------------------------------------------------------------
 *
 *               REGISTRATION
 *
 *      window.fetch  ----------------->     getCreateArgs
 *                                                |
 *   navigator.credentials.create   <-------------'
 *           |
 *           '------------------------->     processCreate
 *                                                |
 *         alert ok or fail      <----------------'
 *
 * ------------------------------------------------------------
 *
 *              VALIDATION
 *
 *      window.fetch ------------------>      getGetArgs
 *                                                |
 *   navigator.credentials.get   <----------------'
 *           |
 *           '------------------------->      processGet
 *                                                |
 *         alert ok or fail      <----------------'
 *
 * ------------------------------------------------------------
 */

// Additional dependencies for MWA
require 'php/mwa.php';

require_once '../src/WebAuthn.php';

try {
    session_start();

    // read get argument and post body
    $fn = filter_input(INPUT_GET, 'fn');
    $requireResidentKey = !!filter_input(INPUT_GET, 'requireResidentKey');
    $userVerification = filter_input(INPUT_GET, 'userVerification', FILTER_SANITIZE_SPECIAL_CHARS);
    $userId = filter_input(INPUT_GET, 'userId', FILTER_SANITIZE_SPECIAL_CHARS);
    $userName = filter_input(INPUT_GET, 'userName', FILTER_SANITIZE_SPECIAL_CHARS);
    $userDisplayName = filter_input(INPUT_GET, 'userDisplayName', FILTER_SANITIZE_SPECIAL_CHARS);

    //$userId = preg_replace('/[^0-9a-f]/i', '', $userId);
    //$userName = preg_replace('/[^0-9a-z]/i', '', $userName);
    //$userDisplayName = preg_replace('/[^0-9a-z öüäéèàÖÜÄÉÈÀÂÊÎÔÛâêîôû]/i', '', $userDisplayName);

    $post = trim(file_get_contents('php://input'));
    if ($post) {
        $post = json_decode($post, null, 512, JSON_THROW_ON_ERROR);
    }

    // Get all the inputs
    if ($fn !== 'getStoredDataHtml') {
        // Formats
        $formats = [];
        if (filter_input(INPUT_GET, 'fmt_android-key')) {
            $formats[] = 'android-key';
        }
        if (filter_input(INPUT_GET, 'fmt_android-safetynet')) {
            $formats[] = 'android-safetynet';
        }
        if (filter_input(INPUT_GET, 'fmt_apple')) {
            $formats[] = 'apple';
        }
        if (filter_input(INPUT_GET, 'fmt_fido-u2f')) {
            $formats[] = 'fido-u2f';
        }
        if (filter_input(INPUT_GET, 'fmt_none')) {
            $formats[] = 'none';
        }
        if (filter_input(INPUT_GET, 'fmt_packed')) {
            $formats[] = 'packed';
        }
        if (filter_input(INPUT_GET, 'fmt_tpm')) {
            $formats[] = 'tpm';
        }

        $rpId = 'localhost';
        if (filter_input(INPUT_GET, 'rpId')) {
            $rpId = filter_input(INPUT_GET, 'rpId', FILTER_VALIDATE_DOMAIN);
            if ($rpId === false) {
                throw new Exception('invalid relying party ID');
            }
        }

        // types selected on front end
        $typeUsb = !!filter_input(INPUT_GET, 'type_usb');
        $typeNfc = !!filter_input(INPUT_GET, 'type_nfc');
        $typeBle = !!filter_input(INPUT_GET, 'type_ble');
        $typeInt = !!filter_input(INPUT_GET, 'type_int');
        $typeHyb = !!filter_input(INPUT_GET, 'type_hybrid');

        // cross-platform: true, if type internal is not allowed
        //                 false, if only internal is allowed
        //                 null, if internal and cross-platform is allowed
        $crossPlatformAttachment = null;
        if (($typeUsb || $typeNfc || $typeBle || $typeHyb) && !$typeInt) {
            $crossPlatformAttachment = true;

        } else if (!$typeUsb && !$typeNfc && !$typeBle && !$typeHyb && $typeInt) {
            $crossPlatformAttachment = false;
        }

        // new Instance of the server library.
        // make sure that $rpId is the domain name.
        $WebAuthn = new lbuchs\WebAuthn\WebAuthn('WebAuthn Library', $rpId, $formats);

        // add root certificates to validate new registrations
        if (filter_input(INPUT_GET, 'solo')) {
            $WebAuthn->addRootCertificates('rootCertificates/solo.pem');
        }
        if (filter_input(INPUT_GET, 'apple')) {
            $WebAuthn->addRootCertificates('rootCertificates/apple.pem');
        }
        if (filter_input(INPUT_GET, 'yubico')) {
            $WebAuthn->addRootCertificates('rootCertificates/yubico.pem');
        }
        if (filter_input(INPUT_GET, 'hypersecu')) {
            $WebAuthn->addRootCertificates('rootCertificates/hypersecu.pem');
        }
        if (filter_input(INPUT_GET, 'google')) {
            $WebAuthn->addRootCertificates('rootCertificates/globalSign.pem');
            $WebAuthn->addRootCertificates('rootCertificates/googleHardware.pem');
        }
        if (filter_input(INPUT_GET, 'microsoft')) {
            $WebAuthn->addRootCertificates('rootCertificates/microsoftTpmCollection.pem');
        }
        if (filter_input(INPUT_GET, 'mds')) {
            $WebAuthn->addRootCertificates('rootCertificates/mds');
        }
    }


    // ------------------------------------
    // request for create arguments
    // ------------------------------------
    if ($fn === 'getCreateArgs') {
        $createArgs = $WebAuthn->getCreateArgs(\hex2bin(bin2hex($userId)), $userName, $userDisplayName, 60*4, $requireResidentKey, $userVerification, $crossPlatformAttachment);

        header('Content-Type: application/json');
        print(json_encode($createArgs));

        // save challange to session. you have to deliver it to processGet later.
        $_SESSION['challenge'] = $WebAuthn->getChallenge();


    // ------------------------------------
    // request for get arguments
    // ------------------------------------
    } else if ($fn === 'getGetArgs') {
        $ids = [];

        /*
        if ($requireResidentKey) {
            if (!isset($_SESSION['registrations']) || !is_array($_SESSION['registrations']) || count($_SESSION['registrations']) === 0) {
                throw new Exception('we do not have any registrations in session to check the registration');
            }

        } else {
        */
            // Search Database for userid's registrations
            // Use PGSQL for registration management
            // Connecting, selecting database
            $dbconn = pg_connect("host=localhost dbname=".$PG_DB_NAME." user=".$PG_DB_USER." password=".$PG_DB_PASS)
            or die('Could not connect: ' . pg_last_error());

            // First check if the user already has 3 keys created
            $check_query = "SELECT * FROM mfa_creds WHERE userid='".$userId."'";
            $check_result = pg_query($dbconn,$check_query);
            while($check_obj = pg_fetch_object($check_result)){
                error_log($check_obj->credentialid);
                $ids[] = base64_decode($check_obj->credentialid);
            };

            // load registrations from session stored there by processCreate.
            // normaly you have to load the credential Id's for a username
            // from the database.
            /*
            if (isset($_SESSION['registrations']) && is_array($_SESSION['registrations'])) {
                foreach ($_SESSION['registrations'] as $reg) {
                    if ($reg->userId === $userId) {
                        $ids[] = $reg->credentialId;
                    }
                }
            }
            */

            if (count($ids) === 0) {
                throw new Exception('No keys registered.');
            }
        /*
        }
        */

        $getArgs = $WebAuthn->getGetArgs($ids, 60*4, $typeUsb, $typeNfc, $typeBle, $typeHyb, $typeInt, $userVerification);

        header('Content-Type: application/json');
        print(json_encode($getArgs));

        // save challange to server session. you have to deliver it to processGet later.
        $_SESSION['challenge'] = $WebAuthn->getChallenge();


    // ------------------------------------
    // process create
    // ------------------------------------
    } else if ($fn === 'processCreate') {
        $clientDataJSON = base64_decode($post->clientDataJSON);
        $attestationObject = base64_decode($post->attestationObject);
        $challenge = $_SESSION['challenge'];

        // processCreate returns data to be stored for future logins.
        // Normaly you have to store the data in a database connected
        // with the user name.
        $data = $WebAuthn->processCreate($clientDataJSON, $attestationObject, $challenge, $userVerification === 'required', true, false);

        // add user infos
        $data->userId = $userId;
        $data->userName = $userName;
        $data->userDisplayName = $userDisplayName;

        /* We don't use php sessions anymore, we use the database
        if (!isset($_SESSION['registrations']) || !array_key_exists('registrations', $_SESSION) || !is_array($_SESSION['registrations'])) {
            $_SESSION['registrations'] = [];
        }
        $_SESSION['registrations'][] = $data;
        */

        // Use PGSQL for registration management
        // Connecting, selecting database
        $dbconn = pg_connect("host=localhost dbname=".$PG_DB_NAME." user=".$PG_DB_USER." password=".$PG_DB_PASS)
        or die('Could not connect: ' . pg_last_error());

        // First check if the user already has 3 keys created
        $check_query = "SELECT count(id) FROM mfa_creds WHERE userid='".$data->userId."'";
        $check_result = pg_query($dbconn,$check_query);
        $check_row = pg_fetch_row($check_result);
        if($check_row[0] >= 3){
            // Too many keys registered for this user
            error_log("3 keys registered. Clear keys to add new keys.");
            throw new Exception('3 keys registered. Clear keys to add new keys.');
        }
        else{
            // New entry
            // do some more error checking here
            $insert_query = "INSERT INTO mfa_creds ( rpid, credentialid, credentialpublickey, userid) VALUES ('www.metawarrior.army','".base64_encode($data->credentialId)."','".base64_encode($data->credentialPublicKey)."', '".$data->userId."');";
            $q_result = pg_query($dbconn,$insert_query);
        }
        pg_close($dbconn);
        
        $msg = 'Key registered!';
        if ($data->rootValid === false) {
            $msg = 'registration ok, but certificate does not match any of the selected root ca.';
        }

        $return = new stdClass();
        $return->success = true;
        $return->msg = $msg;

        header('Content-Type: application/json');
        print(json_encode($return));


    // ------------------------------------
    // proccess get
    // ------------------------------------
    } else if ($fn === 'processGet') {
        $clientDataJSON = base64_decode($post->clientDataJSON);
        $authenticatorData = base64_decode($post->authenticatorData);
        $signature = base64_decode($post->signature);
        $userHandle = base64_decode($post->userHandle);
        $id = base64_decode($post->id);
        $challenge = $_SESSION['challenge'] ?? '';
        $credentialPublicKey = null;

        // Lookup registrations in the database
        // Store registration in the database
        // Connecting, selecting database
        $dbconn = pg_connect("host=localhost dbname=".$PG_DB_NAME." user=".$PG_DB_USER." password=".$PG_DB_PASS)
        or die('Could not connect: ' . pg_last_error());

        $search_query = "SELECT * FROM mfa_creds WHERE credentialid = '".base64_encode($id)."'";
        $search_result = pg_query($dbconn, $search_query);
        if(pg_num_rows($search_result) > 0){
            error_log("processGet: Found Credentials");
            $row = pg_fetch_array($search_result);
            $credentialPublicKey = base64_decode($row['credentialpublickey']);
        }
        pg_close($dbconn);
        
        // looking up correspondending public key of the credential id
        // you should also validate that only ids of the given user name
        // are taken for the login.
        /*
        if (isset($_SESSION['registrations']) && is_array($_SESSION['registrations'])) {
            foreach ($_SESSION['registrations'] as $reg) {
                if ($reg->credentialId === $id) {
                    $credentialPublicKey = $reg->credentialPublicKey;
                    break;
                }
            }
        }
        */

        if ($credentialPublicKey === null) {
            throw new Exception('Could not find registered key.');
        }

        /* Don't need this check anymore because we're querying the database
        // if we have resident key, we have to verify that the userHandle is the provided userId at registration
        if ($requireResidentKey && $userHandle !== hex2bin($reg->userId)) {
            throw new \Exception('userId doesnt match (is ' . bin2hex($userHandle) . ' but expect ' . $reg->userId . ')');
        }
        */

        // process the get request. throws WebAuthnException if it fails
        $WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge, null, $userVerification === 'required');

        $return = new stdClass();
        $return->success = true;

        header('Content-Type: application/json');
        print(json_encode($return));


    // ------------------------------------
    // proccess clear registrations
    // ------------------------------------
    } else if ($fn === 'clearRegistrations') {
        // Delete all user keys from database
        // Connecting, selecting database
        $dbconn = pg_connect("host=localhost dbname=".$PG_DB_NAME." user=".$PG_DB_USER." password=".$PG_DB_PASS)
        or die('Could not connect: ' . pg_last_error());

        // Delete all registrations for userId in database
        $delete_query = "DELETE FROM mfa_creds WHERE userid='".$userId."'";
        $delete_result = pg_query($dbconn,$delete_query);

        pg_close($dbconn);

        // This shouldn't be needed anymore
        //$_SESSION['registrations'] = null; 
        $_SESSION['challenge'] = null;

        $return = new stdClass();
        $return->success = true;
        $return->msg = 'No keys registered.';

        header('Content-Type: application/json');
        print(json_encode($return));


    // ------------------------------------
    // display stored data as HTML
    // ------------------------------------
    } else if ($fn === 'getStoredDataHtml') {
        // This prints an html page with Bootstrap CSS and some styling for dark pages
        // Query database for registered keys
        // Connecting, selecting database
        $dbconn = pg_connect("host=localhost dbname=".$PG_DB_NAME." user=".$PG_DB_USER." password=".$PG_DB_PASS)
        or die('Could not connect: ' . pg_last_error());

        // Delete all registrations for userId in database
        $select_query = "SELECT * FROM mfa_creds WHERE userid='".$userId."'";
        $select_result = pg_query($dbconn,$select_query);
        $registrations = [];
        // Build array for UI
        while($data = pg_fetch_object($select_result)){
            $new_arr = [];
            $new_arr['userId'] = $data->userid;
            $new_arr['credentialId'] = $data->credentialid;
            $new_arr['credentialPublicKey'] = base64_decode($data->credentialpublickey);
            array_push($registrations,$new_arr);
        }

        pg_close($dbconn);

        $html = '<!DOCTYPE html>' . "\n";
        $html .= '<html><head>';
        $html .= '<meta charset="UTF-8"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">';
        $html .= '</head>';
        $html .= '<body class="d-flex text-center text-bg-dark">';
        $html .= '<div class="d-flex w-100 p-2 mx-auto flex-column">';
        $html .= '<div class="container mx-auto mt-5 mb-5 rounded-3 shadow-lg">';
        if (isset($registrations) && is_array($registrations)) {
        //if (isset($_SESSION['registrations']) && is_array($_SESSION['registrations'])) {
            $html .= '<p class="lead mt-2 mb-5">You have <span class="text-warning">' . count($registrations) . '</span> keys registered.</p>';
            //$html .= '<p>There are ' . count($_SESSION['registrations']) . ' registrations in this session:</p>';
            $keyCount = 0;
            foreach ($registrations as $reg) {
            //foreach ($_SESSION['registrations'] as $reg) {
                $keyCount += 1;
                $html .= '<div class="mx-auto row mb-5 justify-content-center">';
                foreach ($reg as $key => $value) {
                    /* We don't need these checks anymore as we just print the public key
                    if (is_bool($value)) {
                        $value = $value ? 'yes' : 'no';

                    } else if (is_null($value)) {
                        $value = 'null';

                    } else if (is_object($value)) {
                        $value = chunk_split(strval($value), 64);

                    } else if (is_string($value) && strlen($value) > 0 && htmlspecialchars($value, ENT_QUOTES) === '') {
                        $value = chunk_split(bin2hex($value), 64);

                    }
                    */
                    if($key == 'credentialPublicKey'){
                        $html .= '<div class="col mx-auto lead fw-bold">Key: <span class="text-warning">' . htmlspecialchars($keyCount) . '</span></div><div class="col mx-auto small"><i>' . nl2br(htmlspecialchars($value)) . '</i></div>';
                    }

                }
                $html .= '</div>';
            }
        } else {
            $html .= '<p class="text-light lead">There are no keys registered.</p>';
        }
        $html .= '</div></div></body></html>';

        header('Content-Type: text/html');
        print $html;


    // ------------------------------------
    // get root certs from FIDO Alliance Metadata Service
    // ------------------------------------
    // I don't think we use this function anymore, but we're leaving it in now just in case
    } else if ($fn === 'queryFidoMetaDataService') {
        $mdsFolder = 'rootCertificates/mds';
        $success = false;
        $msg = null;

        // fetch only 1x / 24h
        $lastFetch = \is_file($mdsFolder .  '/lastMdsFetch.txt') ? \strtotime(\file_get_contents($mdsFolder .  '/lastMdsFetch.txt')) : 0;
        if ($lastFetch + (3600*48) < \time()) {
            $cnt = $WebAuthn->queryFidoMetaDataService($mdsFolder);
            $success = true;
            \file_put_contents($mdsFolder .  '/lastMdsFetch.txt', date('r'));
            $msg = 'successfully queried FIDO Alliance Metadata Service - ' . $cnt . ' certificates downloaded.';

        } else {
            $msg = 'Fail: last fetch was at ' . date('r', $lastFetch) . ' - fetch only 1x every 48h';
        }

        $return = new stdClass();
        $return->success = $success;
        $return->msg = $msg;

        header('Content-Type: application/json');
        print(json_encode($return));
    }


// something went wrong, the UI should print the error message
} catch (Throwable $ex) {
    $return = new stdClass();
    $return->success = false;
    $return->msg = $ex->getMessage();

    header('Content-Type: application/json');
    print(json_encode($return));
}
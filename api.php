<?php

function american_accents_siteversioning_add_call( $request ) {

    try {

        global $aasiteversioning;

        $theabspathmain = ABSPATH;

        $dbprefixes = american_accent_versioning_dbprefixes_generated();

        if(count(american_accent_versioning_data_lists()) >= $dbprefixes['limit']) {

            american_accents_siteversioning_notice(
                "You've reaached the limit of ".$dbprefixes['limit']." version, please remove the unused version.",
                "error"
            );

            return false;
        }

        // check first if wp basic installation template is exists for versioning.
        if(!file_exists($theabspathmain."version/wordpress")) {

            american_accents_siteversioning_notice(
                "Wordpress basic installation template is missing, under version folder, please create a new once or contact your developers.",
                "error"
            );

            return false;
             
        }

        // auto generate version id;
        $versionid = abs( crc32( uniqid() ) );
        $inventorydb = $dbprefixes['inv'].$versionid;
        $wpdb = $dbprefixes['wp'].$versionid;

        // if versionid already exists, generate a new one.
        $theversionid = abs( crc32( uniqid() ) );
        while(file_exists($theabspathmain."version/$versionid")) {
            $theversionid = abs( crc32( uniqid() ) );
        }

        $newwpdb = $dbprefixes['wp']."$versionid";
        $newinvtdb = $dbprefixes['inv']."$versionid";
        $mysqldumpvars = defined('_APP_EXEC_MYSQLDUMP_VARS') ? _APP_EXEC_MYSQLDUMP_VARS : '';

        // database backups tmps
        $invttmpsql = $theabspathmain."version/tmp/$newinvtdb.sql";
        $wptmpsql = $theabspathmain."version/tmp/$newwpdb.sql";

        // execute dump mysql into tmp file
        if(defined( '_APP_EXEC_MYSQL_BIN' ) && defined('_APP_EXEC_WPCLI'))
        {
            // inventory backup tmp
            $cmd = _APP_EXEC_MYSQL_BIN."mysqldump $mysqldumpvars -h "._APP_DB_HOST." -u "._APP_DB_USER." -p"._APP_DB_PASSWORD." "._APP_DB_NAME." > " . $invttmpsql;
            exec($cmd);

            // wordpress backup tmp
            $cmd = _APP_EXEC_MYSQL_BIN."mysqldump $mysqldumpvars -h ".DB_HOST." -u ".DB_USER." -p".DB_PASSWORD." ".DB_NAME." > " . $wptmpsql;
            exec($cmd);

            // database creation
            $aasiteversioning->query( "CREATE DATABASE IF NOT EXISTS $newwpdb" );
            $aasiteversioning->query( "CREATE DATABASE IF NOT EXISTS $newinvtdb" );

            // after creating import database from backup sql tmp files
            // inventory import
            $cmd = _APP_EXEC_MYSQL_BIN."mysql -h "._APP_DB_HOST." -u "._APP_DB_USER." -p"._APP_DB_PASSWORD." ".$newinvtdb." < " . $invttmpsql;
            exec($cmd);

            // wp import
            $cmd = _APP_EXEC_MYSQL_BIN."mysql -h ".DB_HOST." -u ".DB_USER." -p".DB_PASSWORD." ".$newwpdb." < " . $wptmpsql;
            exec($cmd);

            // copy wordpress version template
            $wpinstalltemplate = $theabspathmain.'version/wordpress';
            $wpinstallversion = $theabspathmain.'version/'.$versionid;
            exec( "cp -rp $wpinstalltemplate $wpinstallversion" );


            $wpcliversion = "cd $theabspathmain"."version/$versionid && "._APP_EXEC_WPCLI."wp";

            // set wp config url
            $setwpconfig = array(
                "$wpcliversion config set DB_NAME ".$newwpdb,
                "$wpcliversion config set DB_USER ".DB_USER,
                "$wpcliversion config set DB_PASSWORD ".DB_PASSWORD,
                "$wpcliversion config set DB_HOST ".DB_HOST,

                // inventory db
                "$wpcliversion config set _APP_DB_NAME ".$newinvtdb,
                "$wpcliversion config set _APP_DB_USER "._APP_DB_USER,
                "$wpcliversion config set _APP_DB_PASSWORD "._APP_DB_PASSWORD,
                "$wpcliversion config set _APP_DB_HOST "._APP_DB_HOST,
                "$wpcliversion config set _APP_SUFFIX "._APP_SUFFIX,
                "$wpcliversion config set _APP_DEVMODE "._APP_DEVMODE,

                // version urls
                "$wpcliversion config set WP_CONTENT_URL ".home_url('/wp-content'),
                "$wpcliversion config set WP_HOME ".home_url("/version/$versionid"),
                "$wpcliversion config set WP_SITEURL ".home_url("/version/$versionid"),

                // SALTS
                "$wpcliversion config set JWT_AUTH_SECRET_KEY ".american_accents_siteversioning_hasher('JWT_AUTH_SECRET_KEY'),

                // rewrite flush cache for new version
                "$wpcliversion rewrite flush --hard"
            );

            // execute set wp config
            $cmdwpconfig = join(" ; ", $setwpconfig);
            exec($cmdwpconfig);

            $aasiteversioning->insert($dbprefixes['table'], array(
                'version' => $versionid,
                'commit' => isset($request['commit']) ? $request['commit'] : "executing commit for version $versionid",
                'wpuser' => get_current_user_id(),
                'islive' => 0
            ));

            echo 
                "<div class='notice notice-success is-dismissible ml-0 mr-2'>
                    <p><strong>Version $versionid has been successfully added.</strong></p>
                    <p>Installed WP for version $versionid <a href='".american_accent_versioning_admin_url($versionid)."'>Visit Version</a></p>
                    <p>TMP Databases has been generated.</p>
                    <p>WP Config setup has been generated.</p>
                    <p>Database for $versionid both wp and inventory has been created.</p>
                    <p>Flush Rewrite.</p>
                    <p><a href='javascript:window.location.reload();'>Click Here</a> to refresh the page and view the new added version in the lists.</p>
                </div>"
            ;

             

        } else {

            american_accents_siteversioning_notice(
                "Can not create a new version please check your configuration settings.",
                "error"
            );

            return false;
             
        }

    } catch(\Exception $e) {

        american_accents_siteversioning_notice(
            $e->getMessage(),
            "error"
        );

        return false;
         
    }
}


function american_accents_siteversioning_remove_call( $request ) {

    try {

        $theabspathmain = ABSPATH;

        global $aasiteversioning;

        $dbprefixes = american_accent_versioning_dbprefixes_generated();

        $theversion = isset($request['version']) ? $request['version'] : null;

        if(!$theversion) {
            american_accents_siteversioning_notice(
                "Please select version to remove.",
                "error"
            );

            return false;
             
        }

        if(!count(american_accent_versioning_data_lists(array(
            array(
                'column' => 'version',
                'condition' => '=',
                'value' => "'".$theversion."'"
            )
        )))) {
            american_accents_siteversioning_notice(
                "That version $theversion is not in our record.",
                "error"
            );
             
            return false;
        }

        // perform delete query here
        $aasiteversioning->query( "DELETE FROM ".$dbprefixes['table']." WHERE version='$theversion';" );

        // delete databases

        $wpdbver = $dbprefixes['wp']."$theversion";
        $invdbver = $dbprefixes['inv']."$theversion";

        $aasiteversioning->query( "DROP DATABASE IF EXISTS $wpdbver;" );
        $aasiteversioning->query( "DROP DATABASE IF EXISTS $invdbver;" );


        $execlists = array(
            "rm -rf $theabspathmain"."version/$theversion",
            "rm -rf $theabspathmain"."version/tmp/$wpdbver.sql",
            "rm -rf $theabspathmain"."version/tmp/$invdbver.sql",
        );

        $cmdexec = join(" && ", $execlists);
        exec($cmdexec);

        american_accents_siteversioning_notice(
            "Version $theversion has been removed."
        );

         

    } catch(\Exception $e) {

        american_accents_siteversioning_notice(
            $e->getMessage(),
            "error"
        );

        return false;
         
    }
}



function american_accents_siteversioning_production_call( $request ) {

    try {

        $theabspathmain = ABSPATH;

        global $aasiteversioning;

        $dbprefixes = american_accent_versioning_dbprefixes_generated();

        $theversion = isset($request['version']) ? $request['version'] : null;

        if(!$theversion) {
            american_accents_siteversioning_notice(
                "Please select version to set as live.",
                "error"
            );

            return false;
             
        }

        if(!count(american_accent_versioning_data_lists(array(
            array(
                'column' => 'version',
                'condition' => '=',
                'value' => "'".$theversion."'"
            )
        )))) {
            american_accents_siteversioning_notice(
                "That version $theversion is not in our record.",
                "error"
            );

            return false;
             
        }

        $wpclilive = "cd $theabspathmain && "._APP_EXEC_WPCLI."wp";
        $wpdbver = $dbprefixes['wp']."$theversion";
        $invdbver = $dbprefixes['inv']."$theversion";

        $setwpconfig = array(
            // wp db
            "$wpclilive config set DB_NAME ".$wpdbver,
            // inventory db
            "$wpclilive config set _APP_DB_NAME ".$invdbver
        );

        // execute set wp config
        $cmdwpconfig = join(" ; ", $setwpconfig);
        exec($cmdwpconfig);

        $aasiteversioning->update($dbprefixes['table'], array( 'islive' => 0 ), array('islive' => 1));
        $aasiteversioning->update($dbprefixes['table'], array( 'islive' => 1 ), array('version' => $theversion));
        $aasiteversioning->update($dbprefixes['table'], array( 'schedule' => null ), array('version' => $theversion));


        american_accents_siteversioning_notice(
            "Version $theversion has been set to live/production site <a href='javascript:window.location.reload();'>click Here</a> to refresh the page and view the new live version in the lists."
        );

         

    } catch(\Exception $e) {

        american_accents_siteversioning_notice(
            $e->getMessage(),
            "error"
        );

        return false;

         
    }
}


function american_accent_versioning_data_lists($wheres = []) {

    $dbprefixes = american_accent_versioning_dbprefixes_generated();

    global $aasiteversioning;

    $acceptedKeys = array('id', 'version', 'commit', 'cdate', 'wpuser', 'islive', 'schedule');

    $wheresdatas = array();
    foreach( $wheres as $val) {
        if(in_array($val['column'], $acceptedKeys)) {
            $wheresdatas[] = $val['column'] . $val['condition'] . $val['value'];
        }
    }
    $whereappends = "";
    if(count($wheresdatas)) {
        $whereappends = " WHERE " . join(" AND ", $wheresdatas);
    }

    $results = $aasiteversioning->get_results('SELECT * FROM '.$dbprefixes['table'].' '.$whereappends.' ORDER BY islive DESC, cdate DESC;');

    return $results;
}


function american_accents_siteversioning_migration_query_generator_call( $request ) {

    global $aasiteversioning;
    $querypost = isset($request['query']) ? $request['query'] : null;
    if(!$querypost) {
        echo '';
         
    }

    $querystring = "SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'invt_%'";
    $dblists = $aasiteversioning->get_results($querystring);
    $queryret = "";
    foreach($dblists as $list) {
        $queryconcat = str_replace('[db]', "`".$list->schema_name."`", $querypost);
        $queryret.="
        $queryconcat

        ";
    }
    echo "<code>".nl2br($queryret)."</code>";
     
}

function american_accents_siteversioning_update_call( $request ) {

    try {

        global $aasiteversioning;

        $dbprefixes = american_accent_versioning_dbprefixes_generated();

        if(!isset( $request['version'] )) {

            american_accents_siteversioning_notice(
                "Please select version to update.",
                "error"
            );

            return false;
             
        }

        if(!isset( $request['fields'] ) || !is_array( $request['fields'] )) {
            american_accents_siteversioning_notice(
                "Fields must be array object.",
                "error"
            );

            return false;
             
        }

        if(!count(american_accent_versioning_data_lists(array(
            array(
                'column' => 'version',
                'condition' => '=',
                'value' => "'".$request['version']."'"
            )
        )))) {
            american_accents_siteversioning_notice(
                "Version not in record.",
                "error"
            );

            return false;
             
        }

        foreach( $request['fields'] as $val ) {

            foreach( $val as $key => $value ) {

                $aasiteversioning->update($dbprefixes['table'], array( $key => $value ? $value : NULL ), array('version' => $request['version']));

            }
        }

        american_accents_siteversioning_notice(
            "Saved"
        );

    } catch(\Exception $e) {
        american_accents_siteversioning_notice(
            $e->getMessage(),
            "error"
        );

        return false;
         
    }
}
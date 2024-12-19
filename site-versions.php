<?php 
$sitevernonce = array(
    'add' => wp_create_nonce( 'american_accents_site_versioning_add' ),
    'remove' => wp_create_nonce( 'american_accents_site_versioning_remove' ),
    'production' => wp_create_nonce( 'american_accents_site_versioning_production' ),
    'migration' => wp_create_nonce( 'american_accents_site_versioning_migration' )
);

$dbversionlists = american_accent_versioning_data_lists();

$liveContent = '<span class="aa-live-site ml-2">LIVE</span>';
?>

<style type="text/css">
.aa-live-site {
    color:#fff;
    padding: 5px 10px;
    border-radius: 5px;
    background: #3858e9;
    font-size: 10px;
    text-transform: uppercase;
    font-weight: bold;
    letter-spacing: 1px;
}
</style>

<div class="notice notice-warning m-0 mr-2 mt-3">
    <p><strong>DEVELOPERS ONLY:</strong> For query migration string to execute all inventory database versions. <a href="#" data-siteversion-elem-open="american-accents-versioning-migrate">Click Here</a> to generate sql string for migration.</p>
</div>

<h1 class="wp-heading-inline">Site Versions</h1>
<p>Site versions is limited to <?php echo american_accent_versioning_dbprefixes_generated()['limit']; ?>, please remove those unused version. <a href="#" data-siteversion-elem-open="american-accents-versioning-form">Click Here</a> if you want to add a new version update.</p>
<p>TMP DB Backups are database backups from your first commit (you can download them).</p>
<p>You are required to login to view these versions.</p>
<p>Do not refresh the page when executing these activities, please wait until the task is finished.</p>

<div class="american-accents-versioning-message mb-5"></div>

<form method="post" class="mr-2 american-accents-versioning-form mb-3" style="display:none;">
    <h4><strong>NEW VERSION</strong></h4>
    <input type="hidden" name="action" value="american_accents_siteversioning_add" />
    <input type="hidden" name="nonce" value="<?php echo $sitevernonce['add']; ?>" />
    <textarea class="full-width mb-2" name="commit" rows="3" placeholder="Commit Version" style="width: 100%;"></textarea>
    <button type="submit" value="exec" class="button button-primary american-accents-versioning-btnsubmit" data-text="Execute Version" data-loading="Loading...">Execute Version</button>
    <button type="button" class="button button" data-siteversion-elem-close="american-accents-versioning-form">Cancel</button>
</form>



<form method="post" class="mr-2 american-accents-versioning-migrate mb-3" style="display:none;">
    <h4><strong>SQL String Migration to all versions</strong></h4>
    <input type="hidden" name="action" value="american_accents_siteversioning_migration_query_generator" />
    <input type="hidden" name="nonce" value="<?php echo $sitevernonce['migration']; ?>" />
    <textarea class="full-width mb-2" name="query" rows="6" placeholder="Enter query use [db] as database shortcode." style="width: 100%;"></textarea>
    <button type="submit" value="exec" class="button button-primary american-accents-versioning-btnsubmit" data-text="Generate Query String" data-loading="Loading...">Generate Query String</button>
    <button type="button" class="button button" data-siteversion-elem-close="american-accents-versioning-migrate">Cancel</button>
</form>

<div class="pr-2">
    <table class="wp-list-table widefat fixed striped pages mt-4">
        <thead>
            <tr>
                <th><strong>Version #</strong></th>
                <th><strong>Commit</strong></th>
                <th><strong>Database TMP's</strong></th>
                <th><strong>Date Created</strong></th>
                <th><strong>User</strong></th>
                <th><strong>Live Schedule</strong></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dbversionlists as $sitever): 
                $sitever = (array) $sitever; 
                $wpadminurl = american_accent_versioning_admin_url($sitever['version']);
                if($sitever['islive']) {
                    $wpadminurl = american_accent_versioning_admin_url();
                }
                ?>
            <tr data-site-ver="<?php echo $sitever['version']; ?>">
                <td>
                    <p class="mb-0">Version <?php echo $sitever['version']; ?> <?php if($sitever['islive']): echo $liveContent; endif; ?></p>
                    <p><a href="<?php echo $wpadminurl; ?>">Visit Version</a></p>
                </td>
                <td><p><?php echo $sitever['commit']; ?></p></td>
                <td><p><a href="<?php echo home_url('/version/tmp/wpdy_'.$sitever['version'].'.sql'); ?>" download>Wordpress</a> | <a href="<?php echo home_url('/version/tmp/invt_'.$sitever['version'].'.sql'); ?>" download>Inventory</a></p></td>
                <td><p><?php echo date_format(date_create($sitever['cdate']), "m/d/Y H:i A"); ?></p></td>
                <td>
                    <?php $wpuser = get_userdata($sitever['wpuser']); ?>
                    <?php if($wpuser): ?>
                    <p class="mb-0"><?php echo $wpuser->display_name; ?></p>
                    <p><a href="mailto:<?php echo $wpuser->user_email; ?>"><?php echo $wpuser->user_email; ?></a></p>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </td>
                <td class="dpicker-siteversion">
                    <?php if(!$sitever['islive']): ?>
                    <input type="date" class="flatpickr w-full" style="display:block;width:100%;" value="<?php echo $sitever['schedule']; ?>" data-site-version="<?php echo $sitever['version']; ?>" />
                        
                        <div class="mt-2">
                            <label for="chkbox_<?php echo $sitever['version']; ?>">
                                <input type="checkbox" 
                                id="chkbox_<?php echo $sitever['version']; ?>" 
                                class="sage-sync-checkbox"
                                data-site-version="<?php echo $sitever['version']; ?>" 
                                <?php echo (int) $sitever['sage_sync']>0 ? 'checked' : ''; ?>>
                                Version requires SAGE SYNC
                            </label>
                        </div>

                        <?php if( $sitever['schedule']): ?>
                        <a href="#" data-site-version="<?php echo $sitever['version']; ?>" class="remove-schedule-link mt-2 d-block">Remove Schedule</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td class="aa-site-version-opt">
                    <?php if(!$sitever['islive']): ?>
                        <button class="button mb-2" data-site-version-live="<?php echo $sitever['version']; ?>" data-text="Set to Live" data-loading="Loading...">Set to Live</button>
                        <button class="button mb-2 ml-1" data-site-version-delete="<?php echo $sitever['version']; ?>" data-text="Delete" data-loading="Loading...">Delete</button>
                    <?php else: ?>
                        <?php echo $liveContent; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if(!count($dbversionlists)): ?>
        <p style="text-align: center;">There is no version available.</p>
    <?php endif; ?>
</div>

<script type="text/javascript">
jQuery(document).ready( function($) {

    var wphomeurl = '<?php echo home_url(); ?>';
    var adminajax = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
    var wpjsonurl = `${wphomeurl}/wp-json`;

    $('[data-siteversion-elem-open]').click( function() {
        var elem = $(this).data('siteversion-elem-open');
        $(`.${elem}`).slideDown();
        return false;
    });

    $('[data-siteversion-elem-close]').click( function() {
        var elem = $(this).data('siteversion-elem-close'); console.log(elem);
        $(`.${elem}`).slideUp();
        return false;
    });

    $('.american-accents-versioning-form').submit( function() {
        var btn = $('.american-accents-versioning-btnsubmit');

        btn.prop('disabled', true);
        btn.text(btn.data('loading'));

        alert('Generating new version, please wait and do not close this page. Please click the OK button');

        $.post( adminajax, $(this).serialize(), function( response ) {
            $('.american-accents-versioning-message').html(response);
            $('.american-accents-versioning-form [name="commit"]').val(null);
            $('.american-accents-versioning-form').slideUp();
            btn.prop('disabled', false);
            btn.text(btn.data('text'));
        });
        return false;
    });



    $('.american-accents-versioning-migrate').submit( function() {
        var btn = $('.american-accents-versioning-btnsubmit');

        btn.prop('disabled', true);
        btn.text(btn.data('loading'));

        alert('Generating SQL String.');

        $.post( adminajax, $(this).serialize(), function( response ) {
            $('.american-accents-versioning-message').html(response);
            $('.american-accents-versioning-migrate').slideUp();
            btn.prop('disabled', false);
            btn.text(btn.data('text'));
        });
        return false;
    });


    $('[data-site-version-delete]').click( function() {
        const btn = $(this);
        var version = btn.data('site-version-delete');

        var confirm = window.confirm(`Are you sure you want to remove version ${version}? This will remove all files tmp's and databases on this version.`);
        if(!confirm) {
            return false;
        }
        
        btn.text(btn.data('loading'));
        btn.prop('disabled', true);
        const siteverdeleteparams = {
            nonce: '<?php echo $sitevernonce['remove']; ?>',
            version: version,
            action: 'american_accents_siteversioning_remove'
        };
        $.post( adminajax, siteverdeleteparams, function( response ) {
            btn.prop('disabled', false);
            btn.text(btn.data('text'));
            $('.american-accents-versioning-message').html(response);
            $(`[data-site-ver="${version}"]`).remove();
        });
        return false;
    });


    $('[data-site-version-live]').click( function() {
        const btn = $(this);
        var version = btn.data('site-version-live');

        var confirm = window.confirm(`Are you sure you want to set version ${version} to production/live site? Please confirm that you already added a new version for the current live site as backup.`);
        if(!confirm) {
            return false;
        }

        btn.text(btn.data('loading'));
        btn.prop('disabled', true);
        const siteprodparams = {
            nonce: '<?php echo $sitevernonce['production']; ?>',
            version: version,
            action: 'american_accents_siteversioning_production'
        };
        $.post( adminajax, siteprodparams, function( response ) {
            btn.prop('disabled', false);
            btn.text(btn.data('text'));
            $('.american-accents-versioning-message').html(response);
        });
        return false;
    });


    async function aaVersionUpdate(version, date=null) {
        const params = {
            version: version,
            fields: [{ schedule: date }],
            nonce: '<?php echo $sitevernonce['production']; ?>',
            action: 'american_accents_siteversioning_update'
        };

        return await $.post( adminajax, params);
    }

    flatpickr(".flatpickr", {
        altInput: true,
        altFormat: "m/d/Y h:i K",
        enableTime: true,
        onChange: async function(selectedDates, dateStr, instance) {
            const inputelem = $(instance.input);
            const version = inputelem.data('site-version');
            const date = dateStr;
            const tbrow = inputelem.parent().parent();
            tbrow.css({
                opacity: 0.5,
                'pointer-events': 'none'
            });
            const response = await aaVersionUpdate(version, date);
            window.location.reload();
        }
    });

    $('.remove-schedule-link').click( async function() {
        const tbrow = $(this).parent().parent();
        tbrow.css({
            opacity: 0.5,
            'pointer-events': 'none'
        });
        const version = $(this).data('site-version');
        const response = await aaVersionUpdate(version, null)
        window.location.reload();
    });

    $('.sage-sync-checkbox').change( async function() {
        var checkedValue = $(this).prop('checked');
        var version = $(this).data('site-version');
        var sageSync = checkedValue ? 1 : 0;
        const params = {
            version: version,
            fields: [{ sage_sync: sageSync }],
            nonce: '<?php echo $sitevernonce['production']; ?>',
            action: 'american_accents_siteversioning_update'
        };

        const parent = $(this).parent().parent();
        parent.css({
                opacity: 0.5,
                'pointer-events': 'none'
            });
            
        await $.post( adminajax, params);

        parent.css({
                opacity: 1,
                'pointer-events': 'auto'
            });


    });
});
</script>
<div class="american-accents-site-version-banner" style="background: #536DFE;">
    <p>You are viewing a version site with the version # of <?php echo american_accents_siteversioning_get_site_version(); ?></p>
</div>
<script type="text/javascript">
    jQuery(document).ready( function($) {
        const appendstylesiteversion = `
        <style type="text/css">
            body {
                padding-bottom: 50px;
            }
            .american-accents-site-version-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 50px;
                display: flex;
                justify-content: center;
                align-items: center;
                color: #fff;
                z-index: 999999999999;
            }
            .american-accents-site-version-banner p {
                margin: 0;
            }
            .module-editor-admin {
                bottom: 80px!important;
            }
        </style>
        `;

        $('head').append(appendstylesiteversion);
    });
</script>
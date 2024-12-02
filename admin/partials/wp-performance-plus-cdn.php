<?php if (!defined('ABSPATH')) exit; ?>

<?php
// Initialize active tab with a default value
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'basics';
?>

<div class="wrap">
    <h1><?php _e('CDN Management', 'wp-performance-plus'); ?></h1>
    
    <p class="description">
        <?php _e('Configure your CDN provider to improve content delivery and website performance.', 'wp-performance-plus'); ?>
    </p>

    <h2 class="nav-tab-wrapper">
        <a href="?page=wp-performance-plus-cdn&tab=basics" class="nav-tab <?php echo $active_tab === 'basics' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Basics', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-cdn&tab=cloudflare" class="nav-tab <?php echo $active_tab === 'cloudflare' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Cloudflare', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-cdn&tab=keycdn" class="nav-tab <?php echo $active_tab === 'keycdn' ? 'nav-tab-active' : ''; ?>">
            <?php _e('KeyCDN', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-cdn&tab=bunnycdn" class="nav-tab <?php echo $active_tab === 'bunnycdn' ? 'nav-tab-active' : ''; ?>">
            <?php _e('BunnyCDN', 'wp-performance-plus'); ?>
        </a>
        <a href="?page=wp-performance-plus-cdn&tab=cloudfront" class="nav-tab <?php echo $active_tab === 'cloudfront' ? 'nav-tab-active' : ''; ?>">
            <?php _e('CloudFront', 'wp-performance-plus'); ?>
        </a>
    </h2>

    <div class="tab-content">
        <?php
        switch ($active_tab) {
            case 'basics':
                include plugin_dir_path(dirname(__FILE__)) . 'partials/cdn-providers/basics.php';
                break;
            case 'cloudflare':
                include plugin_dir_path(dirname(__FILE__)) . 'partials/cdn-providers/cloudflare.php';
                break;
            case 'keycdn':
                include plugin_dir_path(dirname(__FILE__)) . 'partials/cdn-providers/keycdn.php';
                break;
            case 'bunnycdn':
                include plugin_dir_path(dirname(__FILE__)) . 'partials/cdn-providers/bunnycdn.php';
                break;
            case 'cloudfront':
                include plugin_dir_path(dirname(__FILE__)) . 'partials/cdn-providers/cloudfront.php';
                break;
        }
        ?>
    </div>
</div> 
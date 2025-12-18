<?php
/**
 * Single Slot template (plugin fallback)
 */

get_header();

the_post();
?>

    <main class="foc-casino">
        <div class="container">
            <div class="foc-casino__wrapper">

                <aside class="foc-casino__sidebar">
                    <?php
                    /**
                     * Sidebar slot card
                     */
                    do_action('foc_sidebar_slot_card');
                    ?>
                </aside>

                <div class="foc-casino__content">

                    <?php
                    /**
                     * Breadcrumbs output
                     */
                    do_action('foc_breadcrumbs');
                    ?>

                    <?php
                    /**
                     * Main title
                     */
                    do_action('foc_main_title');
                    ?>

                    <?php
                    /**
                     * Main content
                     */
                    do_action('foc_main_content');
                    ?>

                </div>
            </div>
        </div>
    </main>

<?php
get_footer();
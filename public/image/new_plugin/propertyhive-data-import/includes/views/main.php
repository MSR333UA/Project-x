<div class="wrap propertyhive">

    <h1><?php _e('Import Data', 'propertyhive'); ?></h1>

    <?php
    	if ( !empty($errors) )
    	{
    		echo '<div class="error"><p><strong>' . implode("<br>", $errors) . '</strong></p></div>';
    	}
    ?>

    <form method="post" enctype="multipart/form-data">

        <?php wp_nonce_field( 'import-applicant-csv', 'applicant_csv_nonce' ); ?>

        <h3>Import Applicants</h3>

        <p>Upload a CSV containing applicant data and their requirements.</p>

        <p><input type="file" name="applicant_csv"></p>

        <input type="submit" class="button-primary" value="Upload Applicant CSV">

    </form>

    <br><br>

    <form method="post" enctype="multipart/form-data">

        <?php wp_nonce_field( 'import-owner-csv', 'owner_csv_nonce' ); ?>

        <h3>Import Owners &amp; Landlords</h3>

        <p>Upload a CSV containing owner and/or landlord data.</p>

        <p><input type="file" name="owner_csv"></p>

        <input type="submit" class="button-primary" value="Upload Vendor/Landlord CSV">

    </form>

    <br><br>

    <form method="post" enctype="multipart/form-data">

        <?php wp_nonce_field( 'import-thirdparty-csv', 'thirdparty_csv_nonce' ); ?>

        <h3>Import Third Parties</h3>

        <p>Upload a CSV containing third parties data.</p>

        <p><input type="file" name="thirdparty_csv"></p>

        <input type="submit" class="button-primary" value="Upload Third Party CSV">

    </form>

    <br><br>

    <form method="post" enctype="multipart/form-data">

        <?php wp_nonce_field( 'import-viewing-csv', 'viewing_csv_nonce' ); ?>

        <h3>Import Viewings</h3>

        <p>Upload a CSV containing viewing data.</p>

        <p><input type="file" name="viewing_csv"></p>

        <input type="submit" class="button-primary" value="Upload Viewing CSV">

    </form>

    <?php
        $taxonomy_names = get_object_taxonomies( 'property' );
        sort($taxonomy_names);
        foreach ( $taxonomy_names as $taxonomy_name )
        {
    ?>
    <br><br>

    <form method="post" enctype="multipart/form-data">

        <?php wp_nonce_field( 'import-' . $taxonomy_name . '-csv', $taxonomy_name . '_csv_nonce' ); ?>

        <h3>Import Custom Fields - <?php echo ucwords(str_replace("_", " ", $taxonomy_name)); ?></h3>

        <p>Upload a CSV containing <?php echo str_replace("_", " ", $taxonomy_name); ?> data.</p>

        <p><input type="file" name="<?php echo $taxonomy_name; ?>_csv"></p>

        <input type="submit" class="button-primary" value="Upload <?php echo ucwords(str_replace("_", " ", $taxonomy_name)); ?> CSV">

    </form>
    <?php
        }
    ?>

</div>
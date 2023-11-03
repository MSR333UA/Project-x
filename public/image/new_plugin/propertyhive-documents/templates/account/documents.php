<?php
/**
 * Documents page within My Account
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/documents.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="propertyhive-documents">

	<?php
		if ( !empty($public_documents) )
		{
			echo '
			<table class="viewings-table upcoming-viewings-table" width="100%">
				<tr>
					<th>' . __( 'Document Name', 'propertyhive' ) . '</th>
					<th>' . __( 'Created', 'propertyhive' ) . '</th>
				</tr>
			';
			foreach ($public_documents as $document)
			{
				echo '<tr>
					<td><a href="' . wp_get_attachment_url($document['attachment_id']) . '" target="_blank">' . $document['name'] . '</a></td>
					<td>' . date("jS F Y", strtotime($document['created_at'])) . '</td>
				</tr>';
			}
			echo '</table>';
		}
		else
		{
			'<p class="propertyhive-info">' . _e( 'No documents to display', 'propertyhive' ) . '</p>';
		}
	?>

</div>

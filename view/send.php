<?php
get_header();
?>
	<section id="primary" class="content-form">
		<main id="main">
			<form action="<?php echo admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data" id="send_order">
				<label for="">Order code</label>
				<input type="text" name="order" value="<?php echo sanitize_text_field(htmlspecialchars($_GET['id'])); ?>" class="form-css">

				<label for="">Name</label>
				<input type="text" name="names" class="form-css">

				<label for="">Surnames</label>
				<input type="text" name="surnames" class="form-css">

				<label for="">DNI</label>
				<input type="text" name="dni" class="form-css">

				<label for="">Amount</label>
				<input type="text" name="amount" value="<?php echo sanitize_text_field(htmlspecialchars($_GET['total'])); ?>" class="form-css">

				<label for="">Way to pay</label>
				<select name="pay" class="form-css" id="">
					<option value="1">Bank deposit</option>
					<option value="2">Transfer</option>
				</select>

				<label for="">Reference number</label>
				<input type="text" name="reference" class="form-css">

				<label for="">Transaction file</label>
				<input type="file" name="file" class="form-css" id="fileupload">

				<button type="submit" class="form-css">Send</button>
			</form>
			<div id="form-success"></div>
		</main><!-- #main -->
	</section><!-- #primary -->
<?php
get_footer();
?>
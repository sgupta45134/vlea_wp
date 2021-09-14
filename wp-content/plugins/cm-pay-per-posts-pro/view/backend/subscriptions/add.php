<form action="<?php echo esc_attr($currentUrl); ?>" method="post" class="cmppp-subscription-add-form">

	<?php if (isset($_GET['success'])): ?>
		<div class="<?php echo ($_GET['success'] ? 'updated' : 'error'); ?>"><p><?php echo htmlspecialchars($_GET['msg']); ?></p></div>
	<?php endif; ?>

	<p><a href="<?php echo esc_attr($currentUrl); ?>" class="add add-new-h2">Add new</a></p>
	<div class="inner <?php echo (isset($_GET['success']) ? 'open' : 'closed'); ?>">
		<label class="cmppp-subscription-add-find-post">Find post: <input type="text" name="post_find" /></label>
		<label class="cmppp-subscription-add-post">Post: <span></span>
			<a href="#" class="cmppp-subscription-add-post-remove">(remove)</a>
			<input type="hidden" name="post_id" /></label>
		<label>User login: <input type="text" name="user_login" /></label>
		<label>Duration: <input type="number" name="number" />
			<select name="unit">
				<option value="min">minutes</option>
				<option value="h">hours</option>
				<option value="d">days</option>
				<option value="w">weeks</option>
				<option value="m">months</option>
				<option value="y">years</option>
				<option value="l">lifetime</option>
			</select>
		</label>
		<label>
			<input type="hidden" name="referer" value="<?php echo $currentUrl; ?>" />
			<input type="hidden" name="nonce" value="<?php echo $nonceAdd; ?>" />
			<input type="submit" value="Add subscription" class="button-primary" />
		</label>
	</div>
</form>
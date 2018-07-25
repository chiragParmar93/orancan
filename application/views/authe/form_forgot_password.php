<?= $template['partials']['alerts'] ?>
<?php echo form_open('authe/forgot-password'); ?>

<input type="email" name="email" placeholder="Enter your email" value="<?= set_value('email')?>">
<input type="submit">

</form>
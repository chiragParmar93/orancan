<!-- Modal -->
<div class="modal fade" id="modal-form-register" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-body">

				<div class="panel panel-form">
					<div class="panel-heading">
						<h3 class="panel-title">Welcome to Elbut Stan!</h3>
					</div>
					<div class="panel-body">

						<?php echo form_open('authe/register-ajax-post', 'id="form-register"'); ?>
							
							<div class="form-group">
								<label>First Name</label>
								<input type="text" class="form-control" name="first_name"  placeholder="Your Name" 
								data-validation="required"
								>
							</div>
							<div class="form-group">
								<label>Last Name</label>
								<input type="text" class="form-control" name="last_name"  placeholder="Your Name" 
								data-validation="required"
								>
							</div>
							<div class="form-group">
								<label>Email</label>
								<input type="email" class="form-control" name="email" placeholder="Email" 
								data-validation="email"
								>
							</div>
							<div class="form-group">
								<label>Password</label>
								<input type="password" class="form-control" name="password" placeholder="********"
								data-validation="required"
								>
							</div>
							<div class="form-group">
								<label>Confirm Password</label>
								<input type="password" class="form-control" placeholder="********"
								data-validation-confirm="password"
								>
							</div>

							<input type="submit" class="btn btn-purple-sm center-block" value="Signup" data-loading-text="Processing...">
						</form>
					</div>
				</div>


			</div>
		</div>
	</div>
</div>
<form action="/" method="post">
	<fieldset>
		<legend>Register an Applicaton</legend>
		<ol>
			<li><label for="application_name">Application name: </label><input type="text" name="application_name" id="application_name"/></li>
			<li><label for="description">Description: </label><textarea name="description" id="description"></textarea></li>
			<li>
				<label for="application_website">Application Website: </label><input type="text" name="application_website" id="application_website"/>
				<div>Where's your application's home page, where users can go to download or use it?</div>
			</li>
			<li><label for="organization">Organization: </label><input type="text" name="organization" id="organization"/></li>
			<li><label for="application_type">Application Type: </label>...</li>
			<li>
				<label for="callback_url">Callback URL: </label><input type="text" name="callback_url" id="callback_url"/>
				<div>Where should we return to after successfully authenticating?</div>
				<div class="small-text">Client prompts your user to return to your application after approving access.</div>
			</li>	
			<li><label for="application_icon">Application Icon: </label><input type="file" name="application_icon" id="application_icon"/></li>
			<li><input type="submit" name="register_application" id="register_application" value="Register Application"/></li>
		</ol>
	</fieldset>
</form>

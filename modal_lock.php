    <div class="modal_lock" id="myModal_lock">
        <div class="modal-content_lock" align="center">
			
			<br>
			<br>
			
			<div><img src="../img/logo.jpg" width="15%" height="15%"></div>
			<br>
			<br>
			
			
			
			<strong style="font-size: 16px;"><i>Current User:&nbsp;<?= $_SESSION['fullname']; ?></i></strong>
			<h2 align="center" style="color: red;">[ SYSTEM LOCK ]</h2>
            <h2>Enter your Password to Unlock Screen</h2>
            <form id="lock_form" class="m-t" role="form">
         
				
				<input type="hidden" class="form-control" name="username" id="username" value="<?= $username; ?>">
			
				
				<div class="form-group">
					<input type="password" class="form-control" name="password" id="password" style="text-align: center"
						   placeholder="Type password here" required >
				</div>	
				

                <button type="submit" class="btn btn-primary block full-width m-b">Unlock</button>
				<a href="../logout.php" class="btn btn-danger block full-width m-b">Log out Current User & Re-login</a>
            </form>
        </div>
    </div>
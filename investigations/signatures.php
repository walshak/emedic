        <div align="right" class="pull-right"> 
		<?php 
				
							//// CONSULTANTS NAMES FOR NOW PENIDG PAYROLLL
				if($lab_cat=='1'){
					echo  '<strong>Approved By</strong>: ' . 'Dr. Lucious Imoh';?>&nbsp; <?php echo '[ Pathologist ]'; ?>
                        <br>	
                    <div align="right"><img src="images/lucius.jpg" alt="" width="65" height="72" /></div>	                    
				<?php }elseif($lab_cat=='2'){
					echo  '<strong>Approved By</strong>: ' . 'Dr. Keneth Onyedibe'; ?>&nbsp; <?php echo '[ Pathologist ]'; ?>
                        <br>	
                    <div align="right"><img src="images/microbiology.jpg" alt="" width="65" height="72" />	</div>			
				<?php }elseif($lab_cat=='4'){
					echo  '<strong>Approved By</strong>: ' . 'Dr. Jatau Ezra Danjuma '; ?>&nbsp; <?php echo '[ Pathologist ]'; ?>
                        <br>	
                    <div align="right"><img src="images/haema.jpg" alt="" width="65" height="72" /></div>	                    
				<?php }else{
					echo  '<strong>Approved By</strong>: ' . $approved_by;
				}
		
		?><br>
       
        </div>
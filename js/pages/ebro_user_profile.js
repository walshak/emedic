/* [ ---- Ebro Admin - user profile ---- ] */

    $(function() {
		//* edit form
		$('.edit_form').click(function(e) {
			e.preventDefault();
			$('.user_form .editable p').hide();
			$('.user_form .editable .hidden_control,.user_form .form_submit').show();
		})
		//* delete user
		$('.remove_user').click(function(e) {
			e.preventDefault();
			bootbox.dialog({
				message: '<div class="text-center lead">Are you sure?</div>',
				title: 'Deleting user John Smith',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
							
							/*$("#del_id").val($(this).attr("del_id"));
			                $("#delete_empform").submit();*/
						}
					}
				}
			});
		})
		
	})
	
<!--Remove Period-->	
$(document).ready(function(){
$(".deletetest").click(function(){  
	$("#del_id").val($(this).attr("del_id"));
			bootbox.dialog({
				message: '<div class="text-center lead">Are you sure?</div>',
				title: 'Delete Fees Period',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
			                $("#delete_prform").submit();
						}
					}
				}
			});
		})
			
		
});
<!--Remove Period-->
<!--Remove Fees Category-->
$(document).ready(function(){
$(".deletefeescat").click(function(){  
	$("#del_id").val($(this).attr("del_id"));
			bootbox.dialog({
				message: '<div class="text-center lead">Are you sure?</br> This will delete all the particular and payment in this category.</div>',
				title: 'Delete Fees Category',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
			                $("#delete_feescatform").submit();
						}
					}
				}
			});
		})
			
		
});
<!--Remove Fees Category-->
<!--Remove Particular-->
$(document).ready(function(){
$(".deletepar").click(function(){  
	$("#del_id").val($(this).attr("del_id"));
			bootbox.dialog({
				message: '<div class="text-center lead">Are you sure?</br> This will delete all the payment in this particular.</div>',
				title: 'Delete Fees Particular',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
			                $("#delete_partform").submit();
						}
					}
				}
			});
		})
			
		
});<!--Remove Particular-->
<!--Remove marks-->

$(document).ready(function(){
$(".deletemarks").click(function(){  
	$("#del_id").val($(this).attr("del_id"));
			bootbox.dialog({
				message: '<div class="text-center lead">Do you want to delete this marks?</div>',
				title: 'Delete Marks',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
			                $("#delete_marksform").submit();
						}
					}
				}
			});
		})
			
		
});
<!--Remove marks-->
<!--Remove Exam-->

$(document).ready(function(){
$(".deleteexam").click(function(){  
	$("#del_id").val($(this).attr("del_id"));
			bootbox.dialog({
				message: '<div class="text-center lead">Are you sure?<br> This will delete all marklist in this exam category</div>',
				title: 'Delete Exam',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
			                $("#delete_examform").submit();
						}
					}
				}
			});
		})
			
		
});
<!--Remove Exam-->
<!--Remove Exam Category-->

$(document).ready(function(){
$(".delete_excat").click(function(){  
	$("#del_id").val($(this).attr("del_id"));
			bootbox.dialog({
				message: '<div class="text-center lead">Do you want to delete thid exam category?<br> </div>',
				title: 'Delete Exam Category',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
			                $("#delete_excatform").submit();
						}
					}
				}
			});
		})
			
		
});
<!--Remove Exam Category-->
<!--Remove Timetable-->

$(document).ready(function(){
$(".deletetime").click(function(){  
	$("#del_id").val($(this).attr("del_id"));
			bootbox.dialog({
				message: '<div class="text-center lead">Do you want to delete this Timetable?<br> </div>',
				title: 'Delete Timetable',
				buttons: {
					cancel: {
						label: "Cancel",
						className: "btn-link",
						callback: function() {
						}
					},
					confirm: {
						label: "Delete",
						className: "btn-primary",
						callback : function() {
			                $("#delete_timeform").submit();
						}
					}
				}
			});
		})
			
		
});
<!--Remove Timetable-->
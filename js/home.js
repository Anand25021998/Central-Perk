var main=function(){
	$(".tbox").keyup(function(key){
		var length=$(".tbox").val().length;
		var charactersleft=100-length;
		$('.counter').text(charactersleft);
		if(charactersleft<0)
		{
			$('#tbtn').attr('disabled',true); 
		}
		else
			$('#tbtn').attr('disabled',false); 
	});
}
$(document).ready(main);
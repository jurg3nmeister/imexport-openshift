/* (c)Bittion | Created: 16/04/2015 | Developer:reyro | Description: cakephp flash message shown in growl ajax mode */
$(document).ready(function() {
//START SCRIPT

//MAIN - START
    fnCreateFlashGrowlMessage();
//MAIN - END

//<div class="alert alert-success" id="flashGrowlMessage" style="display: none;">
//<a class="close" data-dismiss="alert" href="#">×</a>
//<span id="flashGrowlMessageText">
//Módulo creado con exito!	</span>
//</div>

    function fnCreateFlashGrowlMessage() {
        if ($('#flashGrowlMessage').length > 0) {

            var title = 'ERROR';
            var text = $('#flashGrowlMessageText').text();
            var image = 'error.png';

            if($('#flashGrowlMessage').hasClass('alert-success')){
                title = 'EXITO';
                image = 'check.png';
            }

            $.gritter.add({
                title: title,
                text: text,
                sticky: false,
                image:urlImg+image
            });
        }
    }

//END SCRIPT
});

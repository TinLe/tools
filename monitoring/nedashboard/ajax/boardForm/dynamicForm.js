/*
 * Ajax Enabled Form which uses the content of the php form
 * <b>globalFormFile</b> to collect user data asynchron.
 *
 * Autor: Marmsoler Diego
 */

var globalFormFile;
var doPostBack;

function checkForSupportedAjaxBrowser(){
    var navig_agt=navigator.userAgent.toLowerCase();
    var navig_name = navigator.appName.toLowerCase();
    var version=navigator.appVersion;


    if (navig_name.indexOf("netscape") != -1 ) {
        if (parseInt(version) > 4){
            return true;
        } else {
            alert ("netscape "+parseInt(version));	return false;
        }
    } else if (navig_name.indexOf("microsoft internet explorer") != -1 ) {
        if (parseInt(version) >= 4){
            return true;
        } else {
            alert ("microsoft internet explorer "+parseInt(version));
            return false;
        }
    } else {
        alert ("Browser not supported");
        alert (navig_name + " [] " + navig_agt+" [] "+version + " [] " + navigator.appName);
        return false;
    }
}

function openDialogForm(e,formFile,postBack) {
    if (!checkForSupportedAjaxBrowser()){
        alert ("Supported for firefox 3.0<. Escape now...");
        return false;
    }
    globalFormFile=formFile;
    doPostBack=postBack;
    
    //e.preventDefault();
    // load the contact form using ajax
    $.get(formFile, function(data){
        // create a modal dialog with the data
        $(data).modal({
            close: false,
            overlayId: 'contact-overlay',
            containerId: 'contact-container',
            onOpen: contact.open,
            onShow: contact.show,
            onClose: contact.close
        });
    });
}

$(document).ready(function () {
//patrick: preolad of images not needed
    // preload images
    //var img = ['cancel.png','form_bottom.gif','form_top.gif','form_top_ie.gif','loading.gif','send.png'];
    //$(img).each(function () {
    //    var i = new Image();
    //    i.src = 'img/contact/' + this;
    //});
});

var contact = {
    
    message: null,
    open: function (dialog) {
        // add padding to the buttons in firefox/mozilla
        if ($.browser.mozilla) {
            $('#contact-container .contact-button').css({
                'padding-bottom': '2px'
            });
        }
        // input field font size
        if ($.browser.safari) {
            $('#contact-container .contact-input').css({
                'font-size': '.9em'
            });
        }

        var title = $('#contact-container .contact-title').html();
        $('#contact-container .contact-title').html('Loading...');
        dialog.overlay.fadeIn(200, function () {
            dialog.container.fadeIn(200, function () {
                dialog.data.fadeIn(200, function () {
                    $('#contact-container .contact-content').animate({
                        height: 500
                    }, function () {
                        $('#contact-container .contact-title').html(title);
                        $('#contact-container form').fadeIn(200, function () {
                            $('#contact-container #board-name').focus();

                            // fix png's for IE 6
                            if ($.browser.msie && $.browser.version < 7) {
                                $('#contact-container .contact-button').each(function () {
                                    if ($(this).css('backgroundImage').match(/^url[("']+(.*\.png)[)"']+$/i)) {
                                        var src = RegExp.$1;
                                        $(this).css({
                                            backgroundImage: 'none',
                                            filter: 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src="' +  src + '", sizingMethod="crop")'
                                        });
                                    }
                                });
                            }
                        });
                    });
                });
            });
        });
    },
    show: function (dialog) {
        $('#contact-container .contact-send').click(function (e) {
            e.preventDefault();
            // validate form
            if (contact.validate()) {
                $('#contact-container .contact-message').fadeOut(function () {
                    $('#contact-container .contact-message').removeClass('contact-error').empty();
                });
                $('#contact-container .contact-title').html('Writing...');
                $('#contact-container form').fadeOut(200);
                $('#contact-container .contact-content').animate({
                    height: '80px'
                }, function () {
                    $('#contact-container .contact-loading').fadeIn(200, function () {
                        $.ajax({
                            url: globalFormFile,
                            data: $('#contact-container form').serialize() + '&action=send',
                            type: 'post',
                            cache: false,
                            dataType: 'html',
                            complete: function (xhr) {
                                $('#contact-container .contact-loading').fadeOut(200, function () {
                                    $('#contact-container .contact-title').html('Thank you!');
                                    $('#contact-container .contact-message').html(xhr.responseText).fadeIn(200);
                                    contact.close(dialog);
                                });
                            },
                            error: contact.error
                        });
                    });
                });
            }
            else {
                if ($('#contact-container .contact-message:visible').length > 0) {
                    var msg = $('#contact-container .contact-message div');
                    msg.fadeOut(200, function () {
                        msg.empty();
                        contact.showError();
                        msg.fadeIn(200);
                    });
                }
                else {
                    $('#contact-container .contact-message').animate({
                        height: '30px'
                    }, contact.showError);
                }
				
            }
        });
    },
    close: function (dialog) {
        $('#contact-container .contact-message').fadeOut();
        $('#contact-container .contact-title').html('Closing...');
        $('#contact-container form').fadeOut(200);
        $('#contact-container .contact-content').animate({
            height: 40
        }, function () {
            dialog.data.fadeOut(200, function () {
                dialog.container.fadeOut(200, function () {
                    dialog.overlay.fadeOut(200, function () {
                        $.modal.close();
                        if (doPostBack) {
                            window.location.reload(); //window.location.href=unescape(window.location.href);
                        }
                    });
                });
            });
        });
    },
    error: function (xhr) {
        alert(xhr.statusText);
    },
    validate: function () {
        contact.message = '';
        var board_name = document.getElementById('boardId').value;
        if (board_name.length < 2) {
            alert("This name is too short to be used as a new board.\n\nPlease specify a string with at least two characters.");
            return false;
        }
        //Validate for at least on alpha-char and at least on alphanumeric
        var objRegEx = /(^[A-z]{1})([A-z0-9]*$)/;
                
        if (!objRegEx.test(board_name)){
            alert("The name: "+board_name+" is not valid to be used as a new board.\n\nPlease specify a string of only Alphanumeric digits. (Also the leading character has not to be a number)");
            return false;
        }

        if (contact.message.length > 0) {
            return false;
        }
        else {
            return true;
        }
    },
    showError: function () {
        $('#contact-container .contact-message')
        .html($('<div class="contact-error">').append(contact.message))
        .fadeIn(200);
    }
};
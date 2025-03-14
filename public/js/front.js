$(document).ready(function () {

    'use strict';

    // ------------------------------------------------------- //
    // Search Box
    // ------------------------------------------------------ //
    $('#search').on('click', function (e) {
        e.preventDefault();
        $('.search-box').fadeIn();
    });
    $('.dismiss').on('click', function () {
        $('.search-box').fadeOut();
    });

    // ------------------------------------------------------- //
    // Card Close
    // ------------------------------------------------------ //
    $('.card-close a.remove').on('click', function (e) {
        e.preventDefault();
        $(this).parents('.card').fadeOut();
    });

    // ------------------------------------------------------- //
    // Tooltips init
    // ------------------------------------------------------ //    

    $('[data-toggle="tooltip"]').tooltip()    


    // ------------------------------------------------------- //
    // Adding fade effect to dropdowns
    // ------------------------------------------------------ //
    $('.dropdown').on('show.bs.dropdown', function () {
        $(this).find('.dropdown-menu').first().stop(true, true).fadeIn();
    });
    $('.dropdown').on('hide.bs.dropdown', function () {
        $(this).find('.dropdown-menu').first().stop(true, true).fadeOut();
    });


    // ------------------------------------------------------- //
    // Sidebar Functionality
    // ------------------------------------------------------ //
    $('#toggle-btn').on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass('active');

        $('.side-navbar').toggleClass('shrinked');
        $('.content-inner').toggleClass('active');
        $(document).trigger('sidebarChanged');

        if ($(window).outerWidth() > 1183) {
            if ($('#toggle-btn').hasClass('active')) {
                $('.navbar-header .brand-small').hide();
                $('.navbar-header .brand-big').show();
            } else {
                $('.navbar-header .brand-small').show();
                $('.navbar-header .brand-big').hide();
            }
        }

        if ($(window).outerWidth() < 1183) {
            $('.navbar-header .brand-small').show();
        }
    });

    // ------------------------------------------------------- //
    // Universal Form Validation
    // ------------------------------------------------------ //

    $('.form-validate').each(function() {  
        $(this).validate({
            errorElement: "div",
            errorClass: 'is-invalid',
            validClass: 'is-valid',
            ignore: ':hidden:not(.summernote, .checkbox-template, .form-control-custom),.note-editable.card-block',
            errorPlacement: function (error, element) {
                // Add the `invalid-feedback` class to the error element
                error.addClass("invalid-feedback");
                console.log(element);
                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.siblings("label"));
                } 
                else {
                    error.insertAfter(element);
                }
            }
        });

    });    

    // ------------------------------------------------------- //
    // Material Inputs
    // ------------------------------------------------------ //

    var materialInputs = $('input.input-material');

    // activate labels for prefilled values
    materialInputs.filter(function() { return $(this).val() !== ""; }).siblings('.label-material').addClass('active');

    // move label on focus
    materialInputs.on('focus', function () {
        $(this).siblings('.label-material').addClass('active');
    });

    // remove/keep label on blur
    materialInputs.on('blur', function () {
        $(this).siblings('.label-material').removeClass('active');

        if ($(this).val() !== '') {
            $(this).siblings('.label-material').addClass('active');
        } else {
            $(this).siblings('.label-material').removeClass('active');
        }
    });

    // ------------------------------------------------------- //
    // Footer 
    // ------------------------------------------------------ //   

    var contentInner = $('.content-inner');

    $(document).on('sidebarChanged', function () {
        adjustFooter();
    });

    $(window).on('resize', function () {
        adjustFooter();
    })

    function adjustFooter() {
        var footerBlockHeight = $('.main-footer').outerHeight();
        contentInner.css('padding-bottom', footerBlockHeight + 'px');
    }

    // ------------------------------------------------------- //
    // External links to new window
    // ------------------------------------------------------ //
    $('.external').on('click', function (e) {

        e.preventDefault();
        window.open($(this).attr("href"));
    });

    // ------------------------------------------------------ //
    // For demo purposes, can be deleted
    // ------------------------------------------------------ //

    var stylesheet = $('link#theme-stylesheet');
    $("<link id='new-stylesheet' rel='stylesheet'>").insertAfter(stylesheet);
    var alternateColour = $('link#new-stylesheet');

    // if ($.cookie("theme_csspath")) {
    //     alternateColour.attr("href", $.cookie("theme_csspath"));
    // }

    $("#colour").change(function () {

        if ($(this).val() !== '') {

            var theme_csspath = 'css/style.' + $(this).val() + '.css';

            alternateColour.attr("href", theme_csspath);

            $.cookie("theme_csspath", theme_csspath, {
                expires: 365,
                path: document.URL.substr(0, document.URL.lastIndexOf('/'))
            });

        }

        return false;
    });

});


((w, d) => {

    /* funcion global para ver contraseña */
    w.toggleEyeInputId = function (evt, elementId) {
        const iconEye = evt.firstElementChild;
        const inputRef = d.getElementById(elementId);
        const currentType = inputRef.type;

        let currentIcon = 'fa-eye';

        if(currentType === 'text') inputRef.type = 'password';            
        else {
            inputRef.type = 'text';
            currentIcon = 'fa-eye-slash';
        }

        iconEye.className = `far ${currentIcon} fa-lg`;
    }
    /* funcion global para ver contraseña*/

    /* funcion local animar el tiempo solo para 'login.blade.php' */
    function decrementTimeAnimation(elementId, elementBtnId) {
        const domRef = d.getElementById(elementId); // span
        const btnRef = d.getElementById(elementBtnId) ?? false; // button

        if(!domRef) return;

        let currentTime = domRef.textContent.split(':');
        let no_hrs = false; // ver la hora
        
        if(currentTime.length === 2) {
            currentTime = ['00', ...currentTime];
            no_hrs = true;
        }

        const [hrs, min, sec] = currentTime;
        
        let _hrs = Number(hrs),
            _min = Number(min),
            _sec = Number(sec);

        let startAnimation;
        startAnimation = setInterval(initDecrementation, 1000);

        function initDecrementation(str = [0, 0, 0]) {
            _sec--;
            if(_sec === 0 && _min > 0) {
                _sec = 60;
                _min--;
            } 
            
            if(_min === 0 && _hrs > 0) {
                _min = 60;
                _hrs--; 
            }

            const currHrs = (_hrs < 10) ? `0${_hrs}` : _hrs;
            const currMin = (_min < 10) ? `0${_min}` : _min;
            const currSec = (_sec < 10) ? `0${_sec}` : _sec;

            let joinTime = (no_hrs) ? [`${currMin}m`, `${currSec}s`] : 
                                      [`${currMin}h`, `${currMin}m`, `${currSec}s`]; 
            domRef.textContent = joinTime.join(':'); 

            if (_hrs === 0 && _min === 0 && _sec === 0) {
                clearInterval(startAnimation);
                domRef.parentElement.classList.add('d-none');
                // deshabilitar boton
                if(btnRef) btnRef.removeAttribute('disabled');
            }
        }
    }
    decrementTimeAnimation('decrement-animation', 'decrement-timeout-disabled');
    /* funcion local animar el tiempo solo para 'login.blade.php' */

})(window, document);

$(document).ready(function () {
    $('.parallax').parallax();

    $('.dropdown-trigger').dropdown({
        coverTrigger: false,
        hover: true,
        outDuration: 150
    });
});

$(document).ready(function(){
    $('select').formSelect();
});

$(document).ready(function() {
    $('input#input_text, textarea#textarea2').characterCounter();
});